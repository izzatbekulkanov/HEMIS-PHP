<?php

use yii\db\Migration;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\EStudentTaskActivity;
/**
 * Class m201017_042312_alter_table_subject_task
 */
class m201017_042312_alter_table_subject_task_student extends Migration
{
    public function safeUp()
    {
		$this->addColumn(ESubjectTask::tableName(), '_task_type', $this->string(64)->defaultValue(11));
		$this->addColumn(ESubjectTask::tableName(), 'test_duration',$this->integer()->null());
		$this->addColumn(ESubjectTask::tableName(), 'question_count', $this->integer()->null());
		$this->addColumn(ESubjectTaskStudent::tableName(), '_task_type', $this->string(64)->defaultValue(11));
	}

    public function safeDown()
    {
        $this->dropColumn(ESubjectTask::tableName(), '_task_type');
        $this->dropColumn(ESubjectTask::tableName(), 'test_duration');
        $this->dropColumn(ESubjectTask::tableName(), 'question_count');
        $this->dropColumn(ESubjectTaskStudent::tableName(), '_task_type');
    }
	
}
