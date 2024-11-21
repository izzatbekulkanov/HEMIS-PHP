<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseModelId;
use common\components\hemis\HemisResponseProject;
use common\models\employee\EEmployeeAcademicDegree;
use common\models\employee\EEmployeeTraining;
use common\models\science\EProject;
use common\models\science\EPublicationMethodical;
use common\models\science\EPublicationProperty;

class EmployeeTrainingUpdater extends BaseApiUpdater
{
    public static function getSyncData(EEmployeeTraining $model)
    {
        $data = [
            'country' => [
                'code' => $model->country->code,
            ],
            'university' => [
                'code' => self::getUniversity(),
            ],
            'educationYear' => [
                'code' => $model->educationYear->code,
            ],
            'employee' => [
                'id' => $model->employee->_uid,
            ],

            'trainingTypeName' => $model->trainingType->name,
            'trainingContract' => $model->training_contract,
            'trainingStartEnd' => self::getModelDate($model->training_date_start),
            'trainingDateEnd' => self::getModelDate($model->training_date_end),
            'specialityName' => $model->specialty_name,
            'foreignUniversity' => $model->university,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EEmployeeTraining $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_RIAdministrativeEmployee2/';
        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'rIAdministrativeEmployee2-view'], $client->getHeaders())
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

    public static function updateModel(EEmployeeTraining $model, $delete = false)
    {
        $client = self::getApiClient();
        $itemUrl = 'v2/entities/hemishe_RIAdministrativeEmployee2/';

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

            if ($model->employee->_uid == null) {
                EmployeeUpdater::updateModel($model->employee);
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