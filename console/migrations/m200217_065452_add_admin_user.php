<?php

use common\models\system\AdminRole;
use yii\db\Migration;
use yii\helpers\Console;

/**
 * Class m200217_065452_add_admin_user
 */
class m200217_065452_add_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $role = new AdminRole([
            'name' => 'Super Administrator',
            'code' => AdminRole::CODE_SUPER_ADMIN,
            'status' => AdminRole::STATUS_ENABLE
        ]);

        if (!$role->save()) {
            throw new \yii\console\Exception(print_r($role, true));
        }

        if ($password = getenv('ADMIN_USER_PASSWORD')) {

        } else {
            $password = substr(md5(Yii::$app->security->generateRandomString()), 0, 6) . rand(10, 99);
        }

        $user = new \common\models\system\Admin([
            'full_name' => 'Super Admin',
            '_role' => $role->id,
            'login' => \common\models\system\Admin::SUPER_ADMIN_LOGIN,
            'email' => \common\models\system\Admin::SUPER_ADMIN_EMAIL,
            'confirmation' => $password,
        ]);


        if (!$user->save()) {
            $user->link('roles', $role);
            throw new \yii\console\Exception(print_r($user, true));
        } else {
            echo sprintf("Super Admin created with password: %s\n", $password);
            file_put_contents(Yii::getAlias('@console/runtime/.passwd'), $password);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \common\models\system\Admin::deleteAll(['login' => 'admin']);
        \common\models\system\AdminRole::deleteAll(['code' => AdminRole::CODE_SUPER_ADMIN]);
    }

}
