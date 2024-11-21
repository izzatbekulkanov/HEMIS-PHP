<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseModelId;
use common\components\hemis\HemisResponseProject;
use common\models\science\EProject;
use common\models\science\EPublicationMethodical;

class PublicationMethodicalUpdater extends BaseApiUpdater
{
    public static function getSyncData(EPublicationMethodical $model)
    {
        $data = [
            'version' => 1,
            'university' => [
                'code' => self::getUniversity()
            ],
            'name' => $model->name,
            'publisher' => $model->publisher,
            'authorCounts' => $model->author_counts,
            'authors' => $model->authors,
            'parameter' => $model->parameter,
            'isChecked' => $model->is_checked,
            'isCheckedDate' => $model->is_checked_date,
            'issueYear' => $model->issue_year,
            'sourceName' => $model->source_name,
            'filename' => is_array($model->filename) ? json_encode($model->filename) : $model->filename,
            'employee' => [
                'id' => $model->employee->_uid
            ],
            'publicationDatabase' => $model->publicationDatabase ? [
                'code' => $model->publicationDatabase->code
            ] : null,
            'methodicalPublicationType' => $model->methodicalPublicationType ? [
                'code' => $model->methodicalPublicationType->code
            ] : null,
            'educationYear' => $model->educationYear ? [
                'code' => $model->educationYear->code
            ] : null,
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function checkModel(EPublicationMethodical $model, $update = true)
    {
        if (!$model->is_checked && $model->_uid == null) {
            throw new HemisApiError(__('Uslubiy nashr tasdiqlanmagan!'));
        }
        $itemUrl = 'v2/entities/hemishe_EPublicationMethodical/';

        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {

                }
            }

            $response = $client->_client
                ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'ePublicationMethodical-view'], $client->getHeaders())
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

    public static function updateModel(EPublicationMethodical $model, $delete = false)
    {
        if ($model->is_checked || $model->_uid) {
            $client = self::getApiClient();
            $itemUrl = 'v2/entities/hemishe_EPublicationMethodical/';

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