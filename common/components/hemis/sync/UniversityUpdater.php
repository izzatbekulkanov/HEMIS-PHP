<?php

namespace common\components\hemis\sync;

use common\components\Config;
use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisUniversity;
use common\models\structure\EUniversity;

class UniversityUpdater extends BaseApiUpdater
{
    public static function updateModel(EUniversity $model, $delete = false)
    {
        $client = self::getApiClient();

        $url = 'v2/entities/hemishe_EUniversity';

        $item = [
            'code' => $model->code,
            //'name' => $model->getTranslationUzbek('name') ?: $model->name,
            'tin' => $model->tin,
            'address' => $model->address,
        ];

        if ($model->_soato) {
            $item['soato'] = ['code' => $model->_soato];
        }

        if ($model->_ownership) {
            $item['ownership'] = ['code' => $model->_ownership];
        }

        if ($model->_university_form) {
            $item['universityType'] = ['code' => $model->_university_form];
        }

        $response = $client->_client
            ->post($url, json_encode($item), $client->getHeaders())
            ->send();

        return new HemisUniversity($client->processResponse($response));
    }
}