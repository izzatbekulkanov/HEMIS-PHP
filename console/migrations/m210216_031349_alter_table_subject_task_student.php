<?php

use yii\db\Migration;
use common\models\curriculum\ESubjectTask;
/**
 * Class m210216_031349_alter_table_subject_task_student
 */
class m210216_031349_alter_table_subject_task_student extends Migration
{
	public function safeUp()
    {
        $this->addColumn('e_subject_task_student', 'deadline', $this->dateTime());

        $tasks = ESubjectTask::find()->select(['id', 'deadline'])->asArray()->all();

        foreach ($tasks as $task) {
            $updated = $this->db->createCommand(
                "UPDATE e_subject_task_student SET deadline = :deadline WHERE _subject_task=:id",
                $task
            )->execute();
            echo "$updated rows updated\n";
        }

    }

    public function safeDown()
    {
        $this->dropColumn('e_subject_task_student', 'deadline');
    }
}
