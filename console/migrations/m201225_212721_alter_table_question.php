<?php

use yii\db\Migration;
use yii\db\pgsql\Schema;

/**
 * Class m201225_212721_alter_table_question
 */
class m201225_212721_alter_table_question extends Migration
{

    public function safeUp()
    {
        $this->renameTable('e_subject_topic_question', 'e_subject_resource_question');
        $this->addColumn('e_subject_task_student', 'data', $this->json());
        $this->addColumn('e_subject_task_student', 'correct', $this->decimal(5, 1));
        $this->addColumn('e_subject_task_student', 'percent', $this->decimal(4, 1));
        $this->addColumn('e_subject_task_student', 'started_at', $this->dateTime());
        $this->addColumn('e_subject_task_student', 'finished_at', $this->dateTime());
    }

    public function safeDown()
    {
        $this->renameTable('e_subject_resource_question', 'e_subject_topic_question');
        $this->dropColumn('e_subject_task_student', 'percent');
        $this->dropColumn('e_subject_task_student', 'correct');
        $this->dropColumn('e_subject_task_student', 'data');
        $this->dropColumn('e_subject_task_student', 'finished_at');
        $this->dropColumn('e_subject_task_student', 'started_at');
    }

    public function json($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(" " . Schema::TYPE_JSON, $length);
    }

}
