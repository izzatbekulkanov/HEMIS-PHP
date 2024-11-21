<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseModelId;
use common\components\hemis\HemisResponseProject;
use common\models\science\EProject;
use common\models\science\EPublicationAuthorMeta;
use common\models\science\EPublicationMethodical;

class PublicationAuthorMetaUpdater extends BaseApiUpdater
{
    public static function getSyncData(EPublicationAuthorMeta $model)
    {
        $data = [
            'university' => [
                'code' => self::getUniversity()
            ],
            'employee' => [
                'id' => $model->employee->_uid
            ],
            'isMainAuthor' => $model->is_main_author,
            'isCheckedByAuthor' => $model->is_checked_by_author,
        ];

        if ($model->publicationMethodical) {
            $data['publicationTypeTable'] = 'methodic';
            $data['publicationMethodical'] = [
                'id' => $model->publicationMethodical->_uid
            ];
        } else if ($model->publicationScientific) {
            $data['publicationTypeTable'] = 'scientific';
            $data['publicationScientific'] = [
                'id' => $model->publicationScientific->_uid
            ];
        } else if ($model->publicationProperty) {
            $data['publicationTypeTable'] = 'property';
            $data['publicationProperty'] = [
                'id' => $model->publicationProperty->_uid
            ];
        }

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }
        return $data;
    }


    public static function checkModel(EPublicationAuthorMeta $model, $update = true)
    {
        return false;
    }

    public static function updateModel(EPublicationAuthorMeta $model, $delete = false)
    {
        $client = self::getApiClient();

        $itemUrl = 'v2/entities/hemishe_EPublicationAuthorMeta/';

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
            $data = json_encode([self::getSyncData($model)]);

            $response = $client->_client
                ->post($itemUrl, $data, $client->getHeaders())
                ->send();

            $data = $response->getData();

            if ($response->isOk) {
                if ($item = array_pop($data)) {
                    if ($result = new HemisResponseModelId($item)) {
                        $model->updateAttributes([
                            '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                            '_sync_date' => new \DateTime(),
                            '_uid' => $result->id,
                        ]);

                        return $result;
                    }
                }
            }

            throw new HemisApiError($data['error']);
        }
    }
}