<?php

use yii\db\Migration;

/**
 * Class m200708_052405_alter_table_student_meta
 */
class m200708_052405_alter_table_student_meta extends Migration
{
    public function safeUp()
    {
       $this->addColumn('e_student_meta', 'order_number', $this->string(32)->null());
	   $this->addColumn('e_student_meta', 'order_date', $this->date()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_meta', 'order_number');
        $this->dropColumn('e_student_meta', 'order_date');
    }
	
}
