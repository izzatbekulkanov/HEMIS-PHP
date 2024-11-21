<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_certificate_committee}}`.
 */
class m210509_200644_add_column_to_e_certificate_committee_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_certificate_committee}}', 'type', $this->string(64));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_certificate_committee}}', 'type');
    }
}
