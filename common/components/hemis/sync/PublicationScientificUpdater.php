<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseModelId;
use common\components\hemis\HemisResponseProject;
use common\models\science\EProject;
use common\models\science\EPublicationMethodical;
use common\models\science\EPublicationScientific;

class PublicationScientificUpdater extends BaseApiUpdater
{
    public static function getSyncData(EPublicationScientific $model)
    {
        $data = [
            'name' => $model->name,
            'sourceName' => $model->source_name,
            'authors' => $model->authors,
            'doi' => $model->doi,
            'university' => [
                'code' => self::getUniversity(),
            ],
            'country' => [
                'code' => 'UZ',
            ],
            'publicationDatabase' => $model->publicationDatabase ? [
                'code' => $model->publicationDatabase->code,
            ] : null,
            'employee' => $model->employee ? [
                'id' => $model->employee->_uid,
            ] : null,
            'keywords' => $model->keywords,
            'educationYear' => $model->educationYear ? [
                'code' => $model->educationYear->code,
            ] : null,
            'scientificPublicationType' => $model->scientificPublicationType ? [
                'code' => $model->scientificPublicationType->code,
            ] : null,
            'authorCounts' => $model->author_counts,
            'parameter' => $model->parameter,
            'locality' => $model->locality ? [
                'code' => $model->locality->code,
            ] : null,
            'isChecked' => $model->is_checked,
            'isCheckedDate' => self::getModelDate($model->is_checked_date),
            'issueYear' => $model->issue_year,
            'filename' => is_array($model->filename) ? json_encode($model->filename) : $model->filename,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EPublicationScientific $model, $update = true)
    {
        if (!$model->is_checked && $model->_uid == null) {
            throw new HemisApiError(__('Ilmiy nashr tasdiqlanmagan!'));
        }

        $itemUrl = 'v2/entities/hemishe_EPublicationScientific/';
        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'ePublicationScientific-view'], $client->getHeaders())
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

    public static function updateModel(EPublicationScientific $model, $delete = false)
    {
        if ($model->is_checked  || $model->_uid) {
            $client = self::getApiClient();
            $itemUrl = 'v2/entities/hemishe_EPublicationScientific/';

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

                    foreach ($model->publicationAuthors as $item) {
                        PublicationAuthorMetaUpdater::updateModel($item);
                    }

                    return $result;
                }

                throw new HemisApiError($result->message);
            }
        } else if ($delete) {
            return true;
        }
    }
}