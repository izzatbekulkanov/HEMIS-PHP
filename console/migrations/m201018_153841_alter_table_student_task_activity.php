<?php

use yii\db\Migration;
use common\models\curriculum\EStudentTaskActivity;
/**
 * Class m201018_153841_alter_table_student_task_activity
 */
class m201018_153841_alter_table_student_task_activity extends Migration
{
    public function safeUp()
    {
		$this->addColumn(EStudentTaskActivity::tableName(), '_task_type', $this->string(64)->defaultValue(11));
		$this->addColumn(EStudentTaskActivity::tableName(), '_subject_topic', $this->integer()->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'data', 'jsonb');
		$this->addColumn(EStudentTaskActivity::tableName(), 'percent_b', $this->decimal(10, 1)->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'percent_c', $this->decimal(10, 1)->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'count', $this->integer()->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'time', $this->integer()->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'correct', $this->integer()->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'status', $this->string(64));
		$this->addColumn(EStudentTaskActivity::tableName(), 'started_at', $this->integer()->null());
		$this->addColumn(EStudentTaskActivity::tableName(), 'finished_at', $this->integer()->null());
		
		$this->addForeignKey(
            'fk_e_student_task_activity__subject_topic_fkey',
            'e_student_task_activity',
            '_subject_topic',
            'e_curriculum_subject_topic',
            'id',
            'RESTRICT',
            'CASCADE'
        );
	}

    public function safeDown()
    {
        $this->dropForeignKey('fk_e_student_task_activity__subject_topic_fkey', 'e_student_task_activity');

        $this->dropColumn(EStudentTaskActivity::tableName(), '_task_type');
        $this->dropColumn(EStudentTaskActivity::tableName(), '_subject_topic');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'data');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'percent_b');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'percent_c');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'count');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'time');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'correct');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'status');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'started_at');
        $this->dropColumn(EStudentTaskActivity::tableName(), 'finished_at');
	}
}
