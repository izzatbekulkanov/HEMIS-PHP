<?php

use yii\db\Migration;

/**
 * Class m200719_073445_alter_table_student_employment
 */
class m200719_073445_alter_table_student_employment extends Migration
{
    public function safeUp()
    {
       $this->addColumn('e_student_employment', 'position_name', $this->string(256)->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_employment', 'position_name');
    }
	
}
