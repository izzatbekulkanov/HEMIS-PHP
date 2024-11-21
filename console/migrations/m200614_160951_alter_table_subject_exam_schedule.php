<?php

use yii\db\Migration;

/**
 * Class m200614_160951_alter_table_subject_exam_schedule
 */
class m200614_160951_alter_table_subject_exam_schedule extends Migration
{
    
	public function safeUp()
    {
        $this->addColumn('e_subject_exam_schedule', 'final_exam_type', $this->string(64)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_subject_exam_schedule', 'final_exam_type');
    }
	
}
