<?php

use yii\db\Migration;

/**
 * Class m200822_172950_alter_table_system_log
 */
class m200822_172950_alter_table_system_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('e_system_log', 'query', $this->string(2048)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
