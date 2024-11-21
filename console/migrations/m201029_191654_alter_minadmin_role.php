<?php

use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\AdminRoleResource;
use yii\db\Migration;

/**
 * Class m201029_191654_alter_minadmin_role
 */
class m201029_191654_alter_minadmin_role extends Migration
{
    public function safeUp()
    {
        /**
         * @var $role AdminRole
         * @var $resource AdminResource
         */
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MIN_ADMIN])) {
            if ($resource = AdminResource::findOne(['path' => 'employee/account'])) {
                if (!$role->canAccessToResource($resource->path)) {
                    (new AdminRoleResource(['_role' => $role->id, '_resource' => $resource->id]))->save();
                }
            }
        }
    }


    public function safeDown()
    {
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MIN_ADMIN])) {
            if ($resource = AdminResource::findOne(['path' => 'employee/account'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
        }
    }

}
