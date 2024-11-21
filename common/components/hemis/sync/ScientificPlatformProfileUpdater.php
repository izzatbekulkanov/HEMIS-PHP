<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseModelId;
use common\components\hemis\HemisResponseProject;
use common\models\science\EProject;
use common\models\science\EScientificPlatformProfile;

class ScientificPlatformProfileUpdater extends BaseApiUpdater
{
    public static function getSyncData(EScientificPlatformProfile $model)
    {
        $data = [
            'scholarDatabase' => [
                'code' => $model->scientificPlatform->code,
            ],
            'university' => [
                'code' => self::getUniversity(),
            ],
            'link' => $model->profile_link,
            'educationYear' => [
                'code' => $model->educationYear->code,
            ],
            'scientificWorkCount' => $model->publication_work_count,
            'hIndex' => $model->h_index,
            'referenceCount' => $model->citation_count,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EScientificPlatformProfile $model, $update = true)
    {
        if (!$model->is_checked && $model->_uid == null) {
            throw new HemisApiError(__('Ilmiy faollik tasdiqlanmagan!'));
        }
        $itemUrl = 'v2/entities/hemishe_EResearchActivity/';

        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eResearchActivity-view'], $client->getHeaders())
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

    public static function updateModel(EScientificPlatformProfile $model, $delete = false)
    {
        if ($model->is_checked || $model->_uid || $delete) {
            $client = self::getApiClient();
            $itemUrl = 'v2/entities/hemishe_EResearchActivity/';

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
}