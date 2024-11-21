<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_diploma_blank}}`.
 */
class m210602_053252_add_columns_to_e_diploma_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_diploma_blank}}', '_qid', $this->bigInteger());
        $this->addColumn('{{%e_diploma_blank}}', '_sync_diff', 'json');
        $this->addColumn('{{%e_diploma_blank}}', '_sync_date', $this->dateTime());
        $this->addColumn('{{%e_diploma_blank}}', '_sync_status', $this->string(16)->defaultValue('not_checked'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_diploma_blank}}', '_qid');
        $this->dropColumn('{{%e_diploma_blank}}', '_sync_diff');
        $this->dropColumn('{{%e_diploma_blank}}', '_sync_date');
        $this->dropColumn('{{%e_diploma_blank}}', '_sync_status');
    }
}
