<?php

use yii\db\Migration;

/**
 * Class m200628_163446_alter_table_student_meta
 */
class m200628_163446_alter_table_student_meta extends Migration
{
    
	public function safeUp()
    {
        $this->addColumn('e_student_meta', 'diploma_registration', $this->integer(3)->defaultValue(0));
        $this->addColumn('e_student_meta', 'employment_registration', $this->integer(3)->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_meta', 'diploma_registration');
        $this->dropColumn('e_student_meta', 'employment_registration');
    }
	
}
