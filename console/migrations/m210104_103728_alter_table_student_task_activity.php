<?php

use common\models\curriculum\EStudentTaskActivity;
use yii\db\Migration;

/**
 * Class m210104_103728_alter_table_student_task_activity
 */
class m210104_103728_alter_table_student_task_activity extends Migration
{

    public function safeUp()
    {
        $this->dropColumn('e_student_task_activity', 'started_at');
        $this->dropColumn('e_student_task_activity', 'finished_at');
        $this->addColumn('e_student_task_activity', 'started_at', $this->dateTime()->null());
        $this->addColumn('e_student_task_activity', 'finished_at', $this->dateTime()->null());
        $this->alterColumn('e_student_task_activity', 'marked_date', $this->dateTime()->null());
        $this->alterColumn('e_student_task_activity', 'send_date', $this->dateTime()->null());
        $this->alterColumn('e_student_task_activity', 'correct', $this->decimal(10, 1));
        $this->alterColumn('e_student_task_activity', 'comment', $this->text()->null());
    }

    public function safeDown()
    {

    }
}
