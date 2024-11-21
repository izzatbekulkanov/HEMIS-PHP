<?php
/*
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\components\hemis\sync;

use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseDiploma;
use common\models\archive\EAcademicRecord;
use common\models\archive\EDiplomaBlank;
use common\models\curriculum\EducationYear;
use common\models\science\EProject;
use common\models\science\EProjectMeta;
use common\models\structure\EUniversity;
use yii\helpers\Json;

class DiplomaBlankUpdater extends BaseApiUpdater
{
    public static function getSyncData(EDiplomaBlank $model)
    {
        $data = [
            'university' => [
                'code' => EUniversity::findCurrentUniversity()->code
            ],
            'educationType' => [
                'code' => $model->type,
            ],
            'blankYear' => ['code' => $model->year],
            'blankStatus' => ['code' => $model->status],
            'blankNumber' => mb_substr($model->number, 1),
            'blankSeria' => mb_substr($model->number, 0, 1),
            'blankCategory' => ['code' => $model->category],
            'cancelReason' => $model->reason
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }

    public static function importModels()
    {
        $itemUrl = 'v2/services/diplom-blank/get';
        $client = self::getApiClient();

        $response = $client->_client
            ->get(
                $itemUrl,
                ['university' => self::getUniversity(), 'year' => EducationYear::getCurrentYear()->code],
                $client->getHeaders()
            )
            ->send();

        if ($response->isOk) {
            $result = $response->getData();

            $results = EDiplomaBlank::importDataFromApi($result['data']);

            return $results;
        } else {
            if ($response->getStatusCode() === 404) {
                throw new \RuntimeException('API not found');
            }
        }

        return false;
    }

    public static function checkModel(EDiplomaBlank $model, $update = true)
    {
        $url = 'v2/services/diplom-blank/setStatus';

        $data = ['blankCode' => $model->number, 'statusCode' => $model->status];
        try {
            $client = self::getApiClient();

            if ($model->_uid == null) {
                if ($result = self::updateModel($model)) {
                }
            }

            $response = $client->_client
                ->get($url, $data, $client->getHeaders())
                ->send();

            if ($response->getIsOk()) {
                $result = $response->getData();
                $result = $result['data'];

                $data = self::getSyncData($model);

                $diff = $client->getDiffData($data, $result);

                $model->updateAttributes(
                    [
                        '_sync_date' => new \DateTime(),
                        '_sync_diff' => $diff,
                        '_sync_status' => count($diff) ? HemisApiSyncModel::STATUS_DIFFERENT : HemisApiSyncModel::STATUS_ACTUAL,
                    ]
                );

                if (count($diff) && $update) {
                    self::updateModel($model);
                    self::checkModel($model, false);
                }

                return $diff;
            } else {
                if ($response->getStatusCode() == 404) {
                    $model->updateAttributes(
                        [
                            '_sync_date' => new \DateTime(),
                            '_sync_status' => HemisApiSyncModel::STATUS_NOT_FOUND,
                        ]
                    );
                } else {
                    $model->updateAttributes(
                        [
                            '_sync_date' => new \DateTime(),
                            '_sync_diff' => $response->getData(),
                            '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $model->updateAttributes(
                [
                    '_sync_diff' => $e->getMessage(),
                    '_sync_date' => new \DateTime(),
                    '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
                ]
            );
        }

        return [];
    }

    public static function updateModel(EDiplomaBlank $model, $delete = false)
    {
        $client = self::getApiClient();

        $url = 'v2/services/diplom-blank/setStatus';


        $data = ['blankCode' => $model->number, 'statusCode' => $model->status];
        if ($model->status === EDiplomaBlank::STATUS_CANCELLED) {
            $data['reason'] = $model->reason;
        }
        $response = $client->_client
            ->get($url, $data, $client->getHeaders())
            ->send();

        if ($result = $client->processResponse($response)) {
            $options = $model->_options;
            if (isset($result['data'])) {
                $options['version'] = $result['data']['version'];
                $options['university'] = $result['data']['university']['code'];
                $model->updateAttributes(
                    [
                        '_uid' => $result['data']['id'],
                        '_sync_date' => new \DateTime(),
                        '_options' => $options,
                        '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                    ]
                );
            }
        }

        return $result;
    }
}