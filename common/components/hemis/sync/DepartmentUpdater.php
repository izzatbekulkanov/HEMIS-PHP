<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisDepartment;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseDiploma;
use common\models\archive\EAcademicRecord;
use common\models\archive\EStudentDiploma;
use common\models\science\EProject;
use common\models\science\EProjectMeta;
use common\models\structure\EDepartment;
use common\models\structure\EUniversity;
use yii\helpers\Json;

class DepartmentUpdater extends BaseApiUpdater
{
    public static function getSyncData(EDepartment $model)
    {
        return [
            'id' => $model->code,
            'university' => [
                'code' => self::getUniversity()
            ],
            'deparmentType' => $model->structureType ? ['code' => $model->structureType->code] : null,
            'nameUz' => $model->getTranslationUzbek('name'),
            'nameRu' => $model->getTranslationRussian('name'),
        ];
    }


    public static function checkModel(EDepartment $model, $update = true)
    {
        $itemUrl = 'v2/entities/hemishe_EUniversityDepartment/';

        $client = self::getApiClient();

        try {
            $response = $client->_client
                ->get($itemUrl . $model->code, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eUniversityDepartment-view'], $client->getHeaders())
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

    public static function updateModel(EDepartment $model, $delete = false)
    {
        $url = 'v2/entities/hemishe_EUniversityDepartment';
        $client = self::getApiClient();

        if ($delete) {
            $response = $client->_client
                ->delete($url . '/' . $model->code, null, $client->getHeaders())
                ->send();
            if ($response->isOk) {
                return true;
            } elseif ($response->statusCode == '404') {
                return true;
            } else {
                throw new HemisApiError($response->getData()['error']);
            }
        }


        $data = json_encode(self::getSyncData($model));

        $response = $client->_client
            ->post($url, $data, $client->getHeaders())
            ->send();

        if ($result = new HemisDepartment($client->processResponse($response))) {
            $model->updateAttributes([
                '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                '_sync_date' => new \DateTime()
            ]);
        }

        return $result;
    }
}