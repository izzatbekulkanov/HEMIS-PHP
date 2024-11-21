<?php

use yii\db\Migration;

/**
 * Class m201220_151011_alter_table_subject_task_student
 */
class m201220_151011_alter_table_subject_task_student extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('e_subject_task_student', '_subject_task', $this->integer()->null());
        $this->addColumn('e_subject_task_student', '_subject_resource', $this->integer()->null());

        $this->addForeignKey(
            'fk_e_subject_task_student_subject_resource',
            'e_subject_task_student',
            '_subject_resource',
            'e_subject_resource',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_e_subject_task_student_subject_resource', 'e_subject_task_student');
        $this->dropColumn('e_subject_task_student', '_subject_resource');
    }
}
