<?php

use common\components\Config;
use yii\db\Migration;

/**
 * Class m210909_082622_system_config_contract_sum_default
 */
class m210909_082622_system_config_contract_sum_default extends Migration
{
    public function safeUp()
    {
        if (Config::get(Config::CONFIG_COMMON_CONTRACT_CALCULATION) == null) {
            Config::set(Config::CONFIG_COMMON_CONTRACT_CALCULATION, '12');
        }
    }

    public function safeDown()
    {
    }
}
