<?php

use yii\db\Migration;

/**
 * Class m210605_062907_alter_table_e_exam_group
 */
class m210605_062907_alter_table_e_exam_group extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_exam_group', 'finish_at', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_exam_group', 'finish_at');
    }
}
