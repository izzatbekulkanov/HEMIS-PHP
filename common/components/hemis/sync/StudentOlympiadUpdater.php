<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponseModelId;
use common\models\student\EStudentOlympiad;

class StudentOlympiadUpdater extends BaseApiUpdater
{
    public static function getSyncData(EStudentOlympiad $model)
    {
        $data = [
            'university' => [
                'code' => self::getUniversity(),
            ],
            'educationYear' => [
                'code' => $model->educationYear->code,
            ],
            'student' => [
                'id' => $model->student->_uid,
            ],
            'country' => [
                'code' => $model->country->code,
            ],
            'olimpiadaType' => $model->olympiad_type,
            'olimpiadaName' => $model->olympiad_name,
            'olimpiadaSectionName' => $model->olympiad_section_name,
            'olimpiadaPlace' => $model->olympiad_place,
            'olimpiadaPlaceDate' => $model->olympiad_date->format('Y-m-d'),
            'takenPosition' => $model->student_place,
            'diplomaNumber' => $model->diploma_number,
            'diplomaSerial' => $model->diploma_serial,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EStudentOlympiad $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_RIAdministrativeStudent4/';
        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'rIAdministrativeStudent4-view'], $client->getHeaders())
                ->send();

            if ($response->getIsOk()) {
                $result = $response->getData();

                $data = self::getSyncData($model);

                $diff = $client->getDiffData($data, $result);

                $model->updateAttributes([
                    '_sync_date' => new \DateTime(),
                    '_sync_diff' => $diff,
                    '_sync_status' => count($diff) ? HemisApiSyncModel::STATUS_DIFFERENT : HemisApiSyncModel::STATUS_ACTUAL,
                ]);

                if (count($diff) && $update) {
                    self::updateModel($model);
                    self::checkModel($model, false);
                }

                return $diff;
            } else {
                if ($response->getStatusCode() == 404) {
                    $model->updateAttributes([
                        '_sync_date' => new \DateTime(),
                        '_sync_status' => HemisApiSyncModel::STATUS_NOT_FOUND,
                    ]);
                } else {
                    $model->updateAttributes([
                        '_sync_date' => new \DateTime(),
                        '_sync_diff' => $response->getData(),
                        '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
                    ]);
                }
            }

        } catch (\Exception $e) {
            $model->updateAttributes([
                '_sync_diff' => $e->getMessage(),
                '_sync_date' => new \DateTime(),
                '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
            ]);
        }
        return false;
    }

    public static function updateModel(EStudentOlympiad $model, $delete = false)
    {
        $client = self::getApiClient();
        $itemUrl = 'v2/entities/hemishe_RIAdministrativeStudent4/';

        if ($delete) {
            if ($model->_uid) {
                $response = $client->_client
                    ->delete($itemUrl . $model->_uid, null, $client->getHeaders())
                    ->send();
                if ($response->isOk) {
                    return true;
                } elseif ($response->statusCode == '404') {
                    return true;
                } else {
                    throw new HemisApiError($response->getData()['error']);
                }
            }
            return true;
        } else {
            $student = $model->student;
            if ($student->_uid == null || $student->student_id_number == null) {
                $client->updateStudent($student);
            }

            $data = json_encode(self::getSyncData($model));

            $response = $client->_client
                ->post($itemUrl, $data, $client->getHeaders())
                ->send();

            if ($response->statusCode == 404) {
                $model->updateAttributes(['_uid' => null]);
            }

            if ($result = new HemisResponseModelId($client->processResponse($response))) {
                $model->updateAttributes([
                    '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                    '_sync_date' => new \DateTime(),
                    '_uid' => $result->id,
                ]);

                return $result;
            }

            throw new HemisApiError($result->message);
        }
    }
}