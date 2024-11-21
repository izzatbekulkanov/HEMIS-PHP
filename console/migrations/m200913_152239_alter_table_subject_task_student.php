<?php

use yii\db\Migration;

/**
 * Class m200913_152239_alter_table_subject_task_student
 */
class m200913_152239_alter_table_subject_task_student extends Migration
{
    public function safeUp()
    {
        $this->addColumn(\common\models\curriculum\ESubjectTaskStudent::tableName(), 'attempt_count', $this->integer(4)->null());
    }

    public function safeDown()
    {
        $this->dropColumn(\common\models\curriculum\ESubjectTaskStudent::tableName(), 'attempt_count');
    }
}
