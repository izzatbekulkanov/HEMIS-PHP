<?php

use yii\db\Migration;
use common\models\system\AdminRole;
use common\models\system\AdminResource;
use common\models\system\AdminRoleResource;

/**
 * Class m210822_074221_alter_marketing_resource
 */
class m210822_074221_alter_marketing_resource extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MARKETING])) {
            if ($resource = AdminResource::findOne(['path' => 'finance/minimum-wage'])) {
                AdminRoleResource::deleteAll(['_role' => $role->id, '_resource' => $resource->id]);
            }
        }
    }


    public function safeDown()
    {

    }
}
