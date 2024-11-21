<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_student_diploma}}`.
 */
class m210914_055859_add_columns_to_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_student_diploma}}', 'additional_info', $this->string(1000));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_student_diploma}}', 'additional_info');
    }
}
