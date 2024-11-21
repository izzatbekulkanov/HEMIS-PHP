<?php

use yii\db\Migration;
use common\models\system\AdminRole;
use common\models\system\AdminResource;
use common\models\system\AdminRoleResource;
use yii\helpers\Console;

/**
 * Class m210822_064731_create_accounting_department_role
 */
class m210822_064731_create_accounting_department_role extends Migration
{
    public function safeUp()
    {
        if (!AdminRole::findOne(['code' => AdminRole::CODE_ACCOUNTING])) {
            $role = new AdminRole([
                'name' => 'Buxgalteriya',
                'code' => AdminRole::CODE_ACCOUNTING,
                'status' => AdminRole::STATUS_ENABLE
            ]);

            if ($role->save()) {
                echo "{$role->name} created successfully\n";
            }
        }


        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MARKETING])) {
            if ($resource = AdminResource::findOne(['path' => 'finance/minimum-wage'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/scholarship-amount'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/contract-type'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/contract-price'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/contract-price-edit'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/contract-price-foreign'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/contract-price-foreign-edit'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/increased-contract-coef'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/student-contract'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/set-student-contract-type'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/to-set-student-contract-type'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/payment-monitoring'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/payment-monitoring-group'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/paid-contract-fee'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'finance/uzasbo-data'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
        }
    }

    public function safeDown()
    {
        AdminRole::deleteAll(['code' => AdminRole::CODE_ACCOUNTING]);
    }
}
