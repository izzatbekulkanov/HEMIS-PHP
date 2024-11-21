<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseGenerateEmployeeId;
use common\components\hemis\HemisResponseGenerateStudentId;
use common\components\hemis\HemisResponseStudent;
use common\models\employee\EEmployee;
use common\models\science\EDoctorateStudent;
use common\models\structure\EUniversity;

class DoctorateStudentUpdater extends BaseApiUpdater
{
    public static function getSyncData(EDoctorateStudent $model)
    {
        $data = [
            'secondName' => $model->second_name,
            'firstName' => $model->first_name,
            'thirdName' => $model->third_name,
            'birthDate' => self::getModelDate($model->birth_date),
            'dissertationTheme' => $model->dissertation_theme,
            'homeAddress' => $model->home_address,
            'acceptedDate' => self::getModelDate($model->accepted_date),
            'paymentForm' => [
                'code' => $model->paymentForm ? $model->paymentForm->code : null
            ],

            'nationality' => [
                'code' => $model->nationality->code
            ],
            'gender' => [
                'code' => $model->gender ? $model->gender->code : null
            ],
            'country' => [
                'code' => $model->country ? $model->country->code : null
            ],
            'province' => $model->province ? $model->province->name : null,
            'district' => $model->district ? $model->district->name : null,
            'soato' => [
                'code' => $model->district ? $model->district->code : null
            ],
            'doctorateStudentStatus' => [
                'code' => $model->doctorateStudentStatus ? $model->doctorateStudentStatus->code : null
            ],

            'doctoralStudentType' => [
                'code' => $model->doctoralStudentType ? $model->doctoralStudentType->code : null
            ],
            'level' => $model->_level,
            'passportPin' => $model->passport_pin,
            'passportNumber' => $model->passport_number,

            'speciality' => [
                'id' => $model->specialty ? $model->specialty->mainSpecialty->id : null
            ],

            'university' => [
                'code' => EUniversity::findCurrentUniversity()->code
            ],
            'department' => [
                'code' => $model->department ? $model->department->code : null
            ],

            /*'scienceBranch' => [
                'id' => $model->scienceBranch ? $model->scienceBranch->id : null
            ],*/
            'citizenship' => [
                'code' => $model->citizenship ? $model->citizenship->code : null
            ],
            'version' => EDoctorateStudent::VERSION,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }

    public static function generateModelId(EDoctorateStudent $model)
    {
        $client = self::getApiClient();

        $response = $client->_client
            ->post('v2/services/doctoral-student/id', json_encode([
                'data' => [
                    'citizenship' => $model->citizenship->code,
                    'pinfl' => $model->passport_pin,
                    'serial' => $model->passport_number,
                    'year' => self::getModelYear($model->accepted_date),
                    'education_type' => $model->doctoralStudentType->getEducationType(),
                ],
            ]), $client->getHeaders())
            ->send();


        if ($result = new HemisResponseGenerateStudentId($client->processResponse($response))) {
            $model->updateAttributes([
                'student_id_number' => $result->unique_id,
                '_uid' => $result->student['id'],
            ]);
        }

        return $result;
    }

    public static function checkModel(EDoctorateStudent $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_EDoctorateStudent/';


        try {
            $client = self::getApiClient();

            if ($model->_uid == null || $model->student_id_number == null) {
                if ($result = self::generateModelId($model)) {

                }
            }
            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eDoctorateStudent-view'], $client->getHeaders())
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
            echo $e->getMessage() . PHP_EOL;
            $model->updateAttributes([
                '_sync_diff' => $e->getMessage(),
                '_sync_date' => new \DateTime(),
                '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
            ]);
        }

        return false;
    }

    public static function updateModel(EDoctorateStudent $model, $delete = false)
    {
        $client = self::getApiClient();

        $itemUrl = 'v2/entities/hemishe_EDoctorateStudent/';

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
            if ($model->_uid == null || $model->student_id_number == null) {
                if ($result = self::generateModelId($model)) {

                }
            }

            $data = json_encode(self::getSyncData($model));

            $response = $client->_client
                ->post($itemUrl, $data, $client->getHeaders())
                ->send();


            if ($response->statusCode == 404) {
                $model->updateAttributes(['_uid' => null, 'student_id_number' => null]);

                if ($result = self::generateModelId($model)) {

                }

                $response = $client->_client
                    ->post($itemUrl . $model->_uid, $data, $client->getHeaders())
                    ->send();
            };

            if ($result = new HemisResponse($client->processResponse($response))) {
                $model->updateAttributes(['_sync_status' => HemisApiSyncModel::STATUS_ACTUAL, '_sync_date' => new \DateTime()]);

                return $result;
            }

            throw new HemisApiError($result->message);
        }
    }
}