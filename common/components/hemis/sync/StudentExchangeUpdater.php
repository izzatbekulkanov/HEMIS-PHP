<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponseModelId;
use common\models\student\EStudentExchange;

class StudentExchangeUpdater extends BaseApiUpdater
{
    public static function getSyncData(EStudentExchange $model)
    {
        $data = [
            'university' => [
                'code' => self::getUniversity(),
            ],
            'educationYear' => [
                'code' => $model->educationYear->code,
            ],
            'country' => [
                'code' => $model->country->code,
            ],
            'educationType' => [
                'code' => $model->educationType->code,
            ],
            'studentFullname' => $model->full_name,
            'exchangeDocument' => $model->exchange_document,
            'exchangeUniversityName' => $model->university,
            'specialityName' => $model->specialty_name,
            'exchangeType' => $model->exchange_type,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EStudentExchange $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_RIAdministrativeStudent2/';
        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'rIAdministrativeStudent2-view'], $client->getHeaders())
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

    public static function updateModel(EStudentExchange $model, $delete = false)
    {
        $client = self::getApiClient();
        $itemUrl = 'v2/entities/hemishe_RIAdministrativeStudent2/';

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