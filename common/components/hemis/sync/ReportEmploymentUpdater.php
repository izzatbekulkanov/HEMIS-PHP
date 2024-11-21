<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisReportResponse;
use common\models\report\ReportEmployment;

class ReportEmploymentUpdater extends BaseApiUpdater
{
    public static function getSyncData(ReportEmployment $model)
    {
        $data = [
            'university' => [
                'code' => self::getUniversity()
            ],
            'department' => [
                'code' => $model->department->code
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
            'paymentForm' => [
                'code' => $model->_payment_form
            ],
            'gender' => [
                'code' => $model->_gender
            ],
            'workplaceCompatibility' => [
                'code' => $model->workplace_compatibility
            ],

            'graduateInactiveType' => $model->_graduate_inactive_type ? [
                'code' => $model->_graduate_inactive_type
            ] : null,
            'graduateFieldsType' => $model->_graduate_fields_type ? [
                'code' => $model->_graduate_fields_type
            ] : null,
            'qty' => $model->qty,
        ];

        if ($model->workplace_compatibility > 14) {
            $data['graduateInactiveType'] = null;
            $data['graduateFieldsType'] = null;
        }

        return $data;
    }

    public static function updateModel(ReportEmployment $model, $delete = false)
    {
        $url = 'v2/services/employment/graduateList';
        $client = self::getApiClient();

        $data = [];
        foreach (ReportEmployment::find()->all() as $model) {
            $data[] = self::getSyncData($model);
        }
        $data = json_encode(['employments' => $data]);
        //echo $data;

        $response = $client->_client
            ->post($url, $data, $client->getHeaders())
            ->send();

        if ($result = new HemisReportResponse($client->processResponse($response))) {
            if ($result->success) {
                ReportEmployment::updateAll([
                    '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                    '_sync_date' => new \DateTime(),
                    '_sync' => true,
                    '_qid' => null
                ], []);

                return true;
            } else {
                throw new HemisApiError($result->data);
            }
        }

        return false;
    }
}