<?php

use yii\db\Migration;

/**
 * Class m211020_061113_alter_table_e_exam_student
 */
class m211020_061113_alter_table_e_exam_student extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_exam_student', 'ip', $this->string(16));
    }


    public function safeDown()
    {
        $this->dropColumn('e_exam_student', 'ip');
    }

}
