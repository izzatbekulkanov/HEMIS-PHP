<?php
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectTask;
use yii\db\Migration;

/**
 * Class m210530_202023_alter_table_student_task_activity
 */
class m210530_202023_alter_table_student_task_activity extends Migration
{
    public function safeUp()
    {
		EStudentTaskActivity::updateAll([
            'active' => EStudentTaskActivity::STATUS_ENABLE,
        ],
            [
                'in', '_task_type',
                [
                    ESubjectTask::TASK_TYPE_TEST
                ]
            ]
        );
	}

    public function safeDown()
    {
        EStudentTaskActivity::updateAll([
            'active' => EStudentTaskActivity::STATUS_DISABLE,
        ],
            [
                'in', '_task_type',
                [
                    ESubjectTask::TASK_TYPE_TEST
                ]
            ]
        );
    }
}
