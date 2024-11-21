<?php

use common\models\system\AdminRole;
use yii\db\Migration;

/**
 * Class m210218_064348_create_techadmin_user
 */
class m210218_064348_create_techadmin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $password = substr(md5(Yii::$app->security->generateRandomString()), 0, 12) . rand(10, 99);
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_SUPER_ADMIN])) {
            $user = new \common\models\system\Admin([
                'full_name' => 'Tech Admin',
                '_role' => $role->id,
                'login' => \common\models\system\Admin::TECH_ADMIN_LOGIN,
                'email' => 'tech@hemis.uz',
                'confirmation' => $password,
            ]);
            if (!$user->save()) {
                $user->link('roles', $role);
                throw new \yii\console\Exception(print_r($user, true));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \common\models\system\Admin::deleteAll(['login' => \common\models\system\Admin::TECH_ADMIN_LOGIN]);
    }

}
