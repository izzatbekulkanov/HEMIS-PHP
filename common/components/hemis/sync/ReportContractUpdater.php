<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\models\report\ReportContract;

class ReportContractUpdater extends BaseApiUpdater
{
    public static function getSyncData(ReportContract $model)
    {
        return [
            'contractStatistics' => [
                'university' => [
                    'code' => self::getUniversity()
                ],
                'educationYear' => [
                    'code' => $model->_education_year
                ],
                'educationType' => [
                    'code' => $model->_education_type
                ],
                'educationForm' => [
                    'code' => $model->_education_form
                ],
                'faculty' => [
                    'code' => $model->department ? $model->department->code : null
                ],
                'course' => [
                    'code' => $model->_course
                ],
                'semester' => [
                    'code' => '11'
                ],
                'dailyCount' => $model->qty,
                'total' => 0,
                'date' => self::getModelDate($model->date)
            ]
        ];
    }

    public static function updateModel(ReportContract $model, $delete = false)
    {
        $url = 'v2/services/student/contractStatistics';
        $client = self::getApiClient();

        $data = json_encode(self::getSyncData($model));

        $response = $client->_client
            ->post($url, $data, $client->getHeaders())
            ->send();

        if ($result = new HemisResponse($client->processResponse($response))) {
            if ($result->success) {
                $model->updateAttributes([
                    '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                    '_sync_date' => new \DateTime(),
                    '_sync' => true,
                    '_qid' => null
                ]);

                return true;
            } else {
                throw new HemisApiError($result->message);
            }
        }

        return false;
    }
}