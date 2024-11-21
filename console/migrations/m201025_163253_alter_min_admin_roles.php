<?php

use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\AdminRoleResource;
use yii\db\Migration;

/**
 * Class m201025_163253_alter_min_admin_roles
 */
class m201025_163253_alter_min_admin_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_DEAN])) {
            if ($resource = AdminResource::findOne(['path' => 'message/all-messages'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
        }
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MIN_ADMIN])) {
            if ($resource = AdminResource::findOne(['path' => 'curriculum/grade-type'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'curriculum/lesson-pair'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
            if ($resource = AdminResource::findOne(['path' => 'curriculum/marking-system'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
