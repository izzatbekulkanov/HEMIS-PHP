<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_university}}`.
 */
class m210420_154733_add_column_to_e_university_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_university}}', 'accreditation_info', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_university}}', 'accreditation_info');
    }
}
