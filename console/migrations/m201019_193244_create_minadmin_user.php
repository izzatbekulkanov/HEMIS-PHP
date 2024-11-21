<?php

use common\models\system\AdminRole;
use yii\db\Migration;

/**
 * Class m201019_193244_create_minadmin_user
 */
class m201019_193244_create_minadmin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \common\components\AccessResources::parsePermissions();

        if (strpos(getenv('BACKEND_URL'), 'hemis.uz') > 0) {
            if ($role = AdminRole::findOne(['code' => AdminRole::CODE_MIN_ADMIN])) {
                $password = substr(md5(Yii::$app->security->generateRandomString()), 0, 12) . rand(10, 99);

                $user = new \common\models\system\Admin([
                    'full_name' => 'Min Admin',
                    '_role' => $role->id,
                    'login' => AdminRole::CODE_MIN_ADMIN,
                    'email' => 'minadmin@hemis.uz',
                    'confirmation' => $password,
                ]);

                if (!$user->save()) {
                    throw new \yii\console\Exception(print_r($user, true));
                } else {
                    $user->link('roles', $role);
                    echo sprintf("Min Admin created with password: %s\n", $password);
                    file_put_contents(Yii::getAlias('@console/runtime/.minadmin'), $password);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \common\models\system\Admin::deleteAll(['login' => AdminRole::CODE_MIN_ADMIN]);
    }
}
