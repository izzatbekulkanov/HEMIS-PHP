<?php

use common\components\AccessResources;
use common\models\system\Admin;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\AdminRoleResource;
use common\models\system\AdminRoles;
use yii\db\Migration;

/**
 * Class m210111_160750_alter_department_role
 */
class m210111_160750_alter_department_role extends Migration
{
    public function safeUp()
    {
        /**
         * @var $role AdminRole
         * @var $resource AdminResource
         */


        if ($teacher = AdminRole::findOne(['code' => AdminRole::CODE_TEACHER])) {
            if ($role = AdminRole::findOne(['code' => AdminRole::CODE_DEPARTMENT])) {
                echo AdminRoleResource::deleteAll(['_role' => $role->id]) . PHP_EOL;
                $updated = AccessResources::parsePermissions(true);
                sprintf("Updated %d permissions\n", $updated);

                foreach (Admin::findAll([
                    'id' => AdminRoles::find()->select(['_admin'])->where(['_role' => $role->id])->column()
                ]) as $admin) {
                    if (AdminRoles::find()->where(['_admin' => $admin->id, '_role' => $teacher->id])->count() == 0) {
                        if ($admin->link('roles', $teacher)) {
                            sprintf("%s user linked to %s role\n", $admin->full_name, $teacher->name);
                        }
                    }
                }
            }
        }
    }


    public function safeDown()
    {

    }
}
