<?php

use yii\db\Migration;

/**
 * Class m200917_050600_alter_table_subject_task
 */
class m200917_050600_alter_table_subject_task extends Migration
{
    public function safeUp()
    {
        $this->addColumn(\common\models\curriculum\ESubjectTask::tableName(), '_exam_type', $this->string(64)->null());
		$this->addForeignKey(
            'e_subject_task_exam_type_fkey',
            'e_subject_task',
            '_exam_type',
            'h_exam_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn(\common\models\curriculum\ESubjectTask::tableName(), '_exam_type');
    }
}
