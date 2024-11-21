<?php

use yii\db\Migration;

/**
 * Class m210218_030043_alter_table_subject_task_student_unique
 */
class m210218_030043_alter_table_subject_task_student_unique extends Migration
{
    public function safeUp()
	{
		$this->createIndex('e_subject_task_student_exam_type_uniq',
            'e_subject_task_student',
            ['_subject_task','_student','_education_year','_semester','_subject','_final_exam_type'],
            true);
		$this->dropIndex('e_subject_task_student_uniq', 'e_subject_task_student');
	}

    public function safeDown()
    {
       $this->dropIndex('e_subject_task_student_exam_type_uniq', 'e_subject_task_student');
	   $this->createIndex('e_subject_task_student_uniq',
            'e_subject_task_student',
            ['_subject_task','_student','_education_year','_semester','_subject'],
            true);		
    }
}
