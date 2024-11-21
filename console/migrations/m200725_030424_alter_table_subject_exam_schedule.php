<?php

use yii\db\Migration;

/**
 * Class m200725_030424_alter_table_subject_exam_schedule
 */
class m200725_030424_alter_table_subject_exam_schedule extends Migration
{
    public function safeUp()
    {
       $this->alterColumn('e_subject_exam_schedule', 'exam_name', $this->string(64)->null());
	}
	public function safeDown()
    {
       $this->alterColumn('e_subject_exam_schedule', 'exam_name', $this->string(64)->notNull());
	}	
}
