<?php

namespace common\components\hemis\sync;

use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisResponse;
use common\components\hemis\HemisResponseDiploma;
use common\components\hemis\HemisResponseEmployee;
use common\components\hemis\HemisResponseEmployeePosition;
use common\components\hemis\HemisResponseGenerateEmployeeId;
use common\models\archive\EAcademicRecord;
use common\models\archive\EStudentDiploma;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\science\EProject;
use common\models\science\EProjectMeta;
use common\models\structure\EUniversity;
use yii\helpers\Json;

class EmployeeMetaUpdater extends BaseApiUpdater
{
    public static function getSyncData(EEmployeeMeta $model)
    {
        $data = [
            'employee' => [
                'id' => $model->employee->_uid
            ],
            'university' => [
                'code' => self::getUniversity()
            ],
            'department' => [
                'code' => $model->department->code
            ],
            'employeeForm' => [
                'code' => $model->employmentForm->code
            ],
            'employeeStatus' => [
                'code' => $model->employeeStatus->code
            ],
            'employeeType' => [
                'code' => $model->employeeType ? $model->employeeType->code : null
            ],
            'employeeRate' => [
                'code' => $model->employmentStaff->code
            ],
            'employeePosition' => [
                'code' => $model->staffPosition->code
            ],
            'jobStartDate' => self::getModelDate($model->contract_date),
            'jobEndDate' => null,
            'contractDate' => self::getModelDate($model->contract_date),
            'contractNumber' => $model->contract_number,
            'decreeDate' => self::getModelDate($model->decree_date),
            'decreeNumber' => $model->decree_number,
            'tag' => 'v2',
        ];

        if ($model->_uid) {
            $data['id'] = $model->_uid;
        }

        return $data;
    }


    public static function updateModel(EEmployeeMeta $model, $delete = false)
    {
        $client = self::getApiClient();
        $itemUrl = 'v2/entities/hemishe_EEmployeeJob/';

        if ($delete) {
            if ($model->_uid) {
                $response = $client->_client
                    ->delete($itemUrl . $model->_uid, null, $client->getHeaders())
                    ->send();
                if ($response->isOk) {
                    $model->updateAttributes(['_uid' => null]);
                    return true;
                } elseif ($response->statusCode == '404') {
                    $model->updateAttributes(['_uid' => null]);
                    return true;
                } else {
                    throw new HemisApiError($response->getData()['error']);
                }
            }
            return true;
        } else {
            $employee = $model->employee;
            if ($employee->_uid == null || $employee->employee_id_number == null) {
                EmployeeUpdater::updateModel($employee);
            }

            $response = $client->_client
                ->post($itemUrl, json_encode(self::getSyncData($model)), $client->getHeaders())
                ->send();


            if ($result = new HemisResponseEmployeePosition($client->processResponse($response))) {
                $model->updateAttributes([
                    '_uid' => $result->id,
                    '_sync_status' => HemisApiSyncModel::STATUS_ACTUAL,
                    '_sync_date' => new \DateTime()
                ]);

                return $result;
            }

            throw new HemisApiError($result->message);
        }
    }

    public static function checkModel(EEmployeeMeta $model, $update = true)
    {
        $client = self::getApiClient();

        $itemUrl = 'v2/entities/hemishe_EEmployeeJob/';

        try {
            $employee = $model->employee;
            if ($employee->_uid == null || $employee->employee_id_number == null) {
                EmployeeUpdater::updateModel($employee);
            }

            if ($model->_uid == null) {
                EmployeeMetaUpdater::updateModel($model);
            }

            if ($model->_uid) {
                $response = $client->_client
                    ->get($itemUrl . $model->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eEmployeeJob-view'], $client->getHeaders())
                    ->send();

                if ($response->getIsOk()) {
                    $result = $response->getData();
                    $data = self::getSyncData($model);

                    $diff = [];
                    if ($data['employee']['id'] != $result['employee']['id']) {
                        $diff['employee'] = [
                            's' => $data['employee']['id'],
                            'a' => $result['employee']['id'],
                        ];
                    }
                    if ($data['university']['code'] != $result['university']['code']) {
                        $diff['university'] = [
                            's' => $data['university']['code'],
                            'a' => $result['university']['code'],
                        ];
                    }

                    if ($data['jobStartDate'] != $result['jobStartDate']) {
                        $diff['jobStartDate'] = [
                            's' => $data['jobStartDate'],
                            'a' => $result['jobStartDate'],
                        ];
                    }

                    foreach ([
                                 'department',
                                 'employeeForm',
                                 'employeeStatus',
                                 'employeeType',
                                 'employeeRate',
                                 'employeeRate',
                                 'employeePosition',
                             ] as $attribute) {
                        if ($data[$attribute]['code'] != $result[$attribute]['id']) {
                            $diff[$attribute] = [
                                's' => $data[$attribute]['code'],
                                'a' => $result[$attribute]['id'],
                            ];
                        }
                    }

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
                        $model->updateAttributes(['_uid' => null]);
                        if ($update) {
                            EmployeeMetaUpdater::updateModel($model);
                            self::checkModel($model, false);
                        }
                    } else {
                        $model->updateAttributes([
                            '_sync_date' => new \DateTime(),
                            '_sync_diff' => $response->getData(),
                            '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
                        ]);
                    }
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
}