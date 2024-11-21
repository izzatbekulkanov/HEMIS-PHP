<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseDiploma;
use common\models\archive\EAcademicRecord;
use common\models\archive\EStudentDiploma;
use common\models\science\EProject;
use common\models\science\EProjectMeta;
use common\models\structure\EUniversity;
use yii\helpers\Json;

class StudentDiplomaUpdater extends BaseApiUpdater
{
    public static function getSyncData(EStudentDiploma $model)
    {
        $data = [
            'student' => [
                'id' => $model->student->_uid
            ],
            'university' => [
                'code' => EUniversity::findCurrentUniversity()->code
            ],
            'department' => [
                'code' => $model->student->meta ? $model->student->meta->department->code : null
            ],
            'diplomaNumber' => $model->diploma_number,
            'registerNumber' => $model->register_number,
            'registerDate' => self::getModelDate($model->register_date),
            'academicRecord' => Json::encode(EAcademicRecord::getStudentAcademicRecords($model->student)),
        ];

        if ($model->specialty && $model->specialty->mainSpecialty) {
            $data['speciality'] = $model->specialty->mainSpecialty->id;
        }

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EStudentDiploma $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_EStudentDiploma/';

        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eStudentDiploma-view'], $client->getHeaders())
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

    public static function updateModel(EStudentDiploma $model, $delete = false)
    {
        $client = self::getApiClient();

        $url = 'v2/entities/hemishe_EStudentDiploma/';

        if ($delete) {
            if ($model->_uid) {
                $response = $client->_client
                    ->delete($url . $model->_uid, null, $client->getHeaders())
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
                ->post($url, $data, $client->getHeaders())
                ->send();


            if ($result = new HemisResponseDiploma($client->processResponse($response))) {
                $model->updateAttributes([
                    '_uid' => $result->id,
                    '_sync_date' => new \DateTime(),
                    '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                ]);
            }

            return $result;
        }
    }
}