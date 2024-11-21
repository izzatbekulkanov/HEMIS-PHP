<?php

use yii\db\Migration;

/**
 * Class m201102_194424_alter_table_student_meta
 */
class m201102_194424_alter_table_student_meta extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('e_student_meta', '_education_year', $this->string(64)->null());
    } 
	
	public function safeDown()
    {
        $this->alterColumn('e_student_meta', '_education_year', $this->string(64)->notNull());
    }
}
