<?php

use yii\db\Migration;

/**
 * Class m211113_172723_alter_table_report_contract
 */
class m211113_172723_alter_table_report_contract extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('r_contract', 'daily', 'qty');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('r_contract', 'qty', 'daily');
    }
}
