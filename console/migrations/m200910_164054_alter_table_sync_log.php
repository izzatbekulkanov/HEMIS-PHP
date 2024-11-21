<?php

use yii\db\Migration;

/**
 * Class m200910_164054_alter_table_sync_log
 */
class m200910_164054_alter_table_sync_log extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('e_system_sync_log', 'error', $this->string(2048)->null());

    }

    public function safeDown()
    {
    }

}
