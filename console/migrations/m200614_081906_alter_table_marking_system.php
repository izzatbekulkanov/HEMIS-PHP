<?php

use yii\db\Migration;

/**
 * Class m200614_081906_alter_table_marking_system
 */
class m200614_081906_alter_table_marking_system extends Migration
{
    public function safeUp()
    {
        $this->addColumn('h_marking_system', 'count_final_exams', $this->integer(3)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('h_marking_system', 'count_final_exams');
    }
}
