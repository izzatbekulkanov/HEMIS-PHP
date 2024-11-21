<?php

use yii\db\Migration;

/**
 * Class m211106_114149_alter_table_system_config
 */
class m211106_114149_alter_table_system_config extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('e_system_config', 'path', $this->string(256)->unique());
    }

    public function safeDown()
    {
    }
}
