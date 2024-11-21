<?php

use yii\db\Migration;

/**
 * Class m210215_115826_alter_table_performace_unique
 */
class m210215_115826_alter_table_performace_unique extends Migration
{
	public function safeUp()
	{
		$this->createIndex('e_performance_student_exam_uniq',
            'e_performance',
            ['_student','_education_year', '_semester','_subject','_exam_type', '_final_exam_type'],
            true);
		$this->dropIndex('e_performance_student_uniq', 'e_performance');
	}

    public function safeDown()
    {
       $this->dropIndex('e_performance_student_exam_uniq', 'e_performance');
	   $this->createIndex('e_performance_student_uniq',
            'e_performance',
            ['_student','_education_year', '_semester','_subject','_exam_type'],
            true);
    }

}
