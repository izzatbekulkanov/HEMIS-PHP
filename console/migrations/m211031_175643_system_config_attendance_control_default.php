<?php

use yii\db\Migration;
use common\components\Config;

/**
 * Class m211031_175643_system_config_attendance_control_default
 */
class m211031_175643_system_config_attendance_control_default extends Migration
{
    public function safeUp()
    {
        if (Config::get(Config::CONFIG_COMMON_ATTENDANCE_CONTROL) == null) {
            Config::set(Config::CONFIG_COMMON_ATTENDANCE_CONTROL, '1');
        }
    }

    public function safeDown()
    {

    }
}
