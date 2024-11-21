<?php

use yii\db\Migration;
use common\models\curriculum\ESubjectTask;

/**
 * Class m210219_165639_alter_table_subject_task_student_status
 */
class m210219_165639_alter_table_subject_task_student_status extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_subject_task_student', 'final_active', $this->integer(3)->defaultValue(1));
    }
	
	public function safeDown()
    {
        $this->dropColumn('e_subject_task_student', 'final_active');
    }
	
}
