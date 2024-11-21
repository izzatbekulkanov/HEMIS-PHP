<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_student_diploma}}`.
 */
class m210615_122232_add_columns_to_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_student_diploma}}', 'graduate_qualifying_work', $this->string(300));
        $this->addColumn('{{%e_student_diploma}}', 'moved_hei', $this->string(1000));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_student_diploma}}', 'graduate_qualifying_work');
        $this->dropColumn('{{%e_student_diploma}}', 'moved_hei');
    }
}
