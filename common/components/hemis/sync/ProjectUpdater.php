<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseProject;
use common\models\science\EProject;

class ProjectUpdater extends BaseApiUpdater
{
    public static function getSyncData(EProject $model)
    {
        $data = [

            'version' => 1,

            'contractDate' => self::getModelDate($model->contract_date),
            'startDate' => self::getModelDate($model->start_date),
            'endDate' => self::getModelDate($model->end_date),
            'university' => [
                'code' => self::getUniversity()
            ],
            'projectCurrency' => [
                'code' => $model->projectCurrency->code
            ],
            'projectType' => [
                'code' => $model->projectType->code
            ],
            'locality' => [
                'code' => $model->locality->code
            ],
            'department' => [
                'code' => $model->department->code
            ],
            'name' => $model->name,
            'projectNumber' => $model->project_number,
            'contractNumber' => $model->contract_number,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EProject $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_EProject/';

        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eProject-view'], $client->getHeaders())
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

    public static function updateModel(EProject $model, $delete = false)
    {
        $client = self::getApiClient();

        $itemUrl = 'v2/entities/hemishe_EProject/';

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

            if ($model->_uid) {
                $response = $client->_client
                    ->put($itemUrl . $model->_uid, $data, $client->getHeaders())
                    ->send();
            } else {
                $response = $client->_client
                    ->post($itemUrl . $model->_uid, $data, $client->getHeaders())
                    ->send();
            }


            if ($response->statusCode == 404) {
                $model->updateAttributes(['_uid' => null]);
            }

            if ($result = new HemisResponseProject($client->processResponse($response))) {
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