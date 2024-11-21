<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseDiploma;
use common\components\hemis\HemisResponseEmployee;
use common\components\hemis\HemisResponseGenerateEmployeeId;
use common\models\archive\EAcademicRecord;
use common\models\archive\EStudentDiploma;
use common\models\employee\EEmployee;
use common\models\science\EProject;
use common\models\science\EProjectMeta;
use common\models\structure\EUniversity;
use yii\helpers\Json;

class EmployeeUpdater extends BaseApiUpdater
{
    public static function getSyncData(EEmployee $model)
    {
        return [
            'id' => $model->_uid,
            'firstname' => $model->first_name,
            'lastname' => $model->second_name,
            'fathername' => $model->third_name,
            'serialNumber' => $model->passport_number,
            'pinfl' => $model->passport_pin,
            'employeeYear' => $model->year_of_enter,
            'birthday' => self::getModelDate($model->birth_date),

            'code' => $model->employee_id_number,

            'gender' => [
                'code' => $model->gender->code
            ],
            'university' => [
                'code' => self::getUniversity()
            ],

            'academicDegree' => [
                'code' => $model->academicDegree->code
            ],
            'academicRank' => [
                'code' => $model->academicRank->code
            ],
        ];
    }


    public static function checkModel(EEmployee $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_ETeacher/';
        $client = self::getApiClient();
        try {
            if ($model->_uid == null || $model->employee_id_number == null) {
                self::generateEmployeeId($model);
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eTeacher-view'], $client->getHeaders())
                ->send();

            if ($response->getIsOk()) {
                $result = $response->getData();
                $data = self::getSyncData($model);

                if ($result['code'] != $data['code']) {
                    if ($model->updateAttributes(['employee_id_number' => $result['code']])) {
                        $data['code'] = $result['code'];
                    }
                }

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

    public static function updateModel(EEmployee $model, $delete = false)
    {
        $client = self::getApiClient();

        $itemUrl = 'v2/entities/hemishe_ETeacher/';

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
            if ($model->_uid == null || $model->employee_id_number == null) {
                self::generateEmployeeId($model);
            }

            $data = self::getSyncData($model);

            $response = $client->_client
                ->post($itemUrl, json_encode($data), $client->getHeaders())
                ->send();


            if ($response->statusCode == 404) {
                $model->updateAttributes(['_uid' => null, 'employee_id_number' => null]);

                $response = $client->_client
                    ->put($itemUrl . $model->_uid, json_encode($data), $client->getHeaders())
                    ->send();
            }

            if ($result = new HemisResponseEmployee($client->processResponse($response))) {
                $model->updateAttributes([
                    '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                    '_sync_date' => new \DateTime()
                ]);

                return $result;
            }

            throw new HemisApiError($result->message);
        }
    }

    public function generateEmployeeId(EEmployee $model)
    {
        $client = self::getApiClient();

        if ($model->citizenshipType == null) {
            throw new HemisApiError(__('Citizenship not given'));
        }

        if ($model->gender == null) {
            throw new HemisApiError(__('Gender not given'));
        }

        if ($model->year_of_enter == null) {
            throw new HemisApiError(__('Enter of year not given'));
        }

        $response = $client->_client
            ->post('v2/services/teacher/id', json_encode([
                'data' => [
                    'citizenship' => $model->citizenshipType->code,
                    'pinfl' => $model->passport_pin,
                    'serial' => $model->passport_number,
                    'gender' => $model->gender->code,
                    'year' => $model->year_of_enter,
                ],
            ]), $client->getHeaders())
            ->send();


        if ($result = new HemisResponseGenerateEmployeeId($client->processResponse($response))) {
            $model->updateAttributes([
                'employee_id_number' => $result->unique_id,
                '_uid' => $result->teacher['id'],
            ]);
        }

        return $result;
    }

}