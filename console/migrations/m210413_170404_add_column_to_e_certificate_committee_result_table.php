<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_certificate_committee_result}}`.
 */
class m210413_170404_add_column_to_e_certificate_committee_result_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_certificate_committee_result}}', 'ball', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_certificate_committee_result}}', 'ball');
    }
}
