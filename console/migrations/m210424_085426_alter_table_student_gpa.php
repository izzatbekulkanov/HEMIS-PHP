<?php

use yii\db\Migration;

/**
 * Class m210424_085426_alter_table_student_gpa
 */
class m210424_085426_alter_table_student_gpa extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_gpa', 'debt_subjects', $this->integer(4)->defaultValue(0));
        $this->addColumn('e_student_gpa', 'can_transfer', $this->boolean()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_gpa', 'debt_subjects');
        $this->dropColumn('e_student_gpa', 'can_transfer');
    }
}
