<?php

use yii\db\Migration;

/**
 * Class m211204_162957_alter_table_e_exam_student
 */
class m211204_162957_alter_table_e_exam_student extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_exam_student', 'session', $this->string(64));
    }

    public function safeDown()
    {
        $this->dropColumn('e_exam_student', 'session');
    }
}
