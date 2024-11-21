<?php

use yii\db\Migration;
use common\models\system\AdminRole;
use common\models\system\AdminResource;
use common\models\system\AdminRoleResource;
/**
 * Class m210920_162421_change_minadmin_role_resource
 */
class m210920_162421_change_minadmin_role_resource extends Migration
{
    public function safeUp()
    {
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MIN_ADMIN])) {
            if ($resource = AdminResource::findOne(['path' => 'transfer/graduate'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
        }
    }

    public function safeDown()
    {

    }
}
