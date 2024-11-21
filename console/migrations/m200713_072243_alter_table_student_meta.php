<?php

use yii\db\Migration;

/**
 * Class m200713_072243_alter_table_student_meta
 */
class m200713_072243_alter_table_student_meta extends Migration
{
    public function safeUp()
    {
       $this->addColumn('e_student_meta', '_status_change_reason', $this->string(64)->null());
	}

    public function safeDown()
    {
        $this->dropColumn('_status_change_reason', 'order_number');
    }
}
