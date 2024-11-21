<?php

use yii\db\Migration;

/**
 * Class m200710_035748_alter_table_academic_record
 */
class m200710_035748_alter_table_academic_record extends Migration
{
     public function safeUp()
    {
       $this->addColumn('e_academic_record', '_employee', $this->integer()->notNull());
	   $this->addColumn('e_academic_record', 'employee', $this->string(256)->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('e_academic_record', '_employee');
        $this->dropColumn('e_academic_record', 'employee');
    }
}
