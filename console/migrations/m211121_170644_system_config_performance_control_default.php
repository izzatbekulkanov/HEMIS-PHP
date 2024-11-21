<?php

use yii\db\Migration;
use common\components\Config;

/**
 * Class m211121_170644_system_config_performance_control_default
 */
class m211121_170644_system_config_performance_control_default extends Migration
{
    public function safeUp()
    {
        if (Config::get(Config::CONFIG_COMMON_PERFORMANCE_CONTROL) == null) {
            Config::set(Config::CONFIG_COMMON_PERFORMANCE_CONTROL, '1');
        }
    }

    public function safeDown()
    {

    }
}
