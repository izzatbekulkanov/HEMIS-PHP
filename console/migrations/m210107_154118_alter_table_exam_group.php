<?php

use yii\db\Migration;

/**
 * Class m210107_154118_alter_table_exam_group
 */
class m210107_154118_alter_table_exam_group extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_exam_group', 'id', $this->primaryKey());
        $this->addColumn('e_exam_group', 'start_at', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_exam_group', 'id');
        $this->dropColumn('e_exam_group', 'start_at');
    }
}
