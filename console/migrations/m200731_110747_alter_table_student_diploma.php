<?php

use yii\db\Migration;

/**
 * Class m200731_110747_alter_table_student_diploma
 */
class m200731_110747_alter_table_student_diploma extends Migration
{
   public function safeUp()
   {
		$this->addColumn('e_student_diploma', '_department', $this->integer()->notNull());
		$this->addColumn('e_student_diploma', 'department', $this->string(256)->notNull());
        
		$this->addForeignKey(
            'fk_e_department_e_student_diploma_fkey',
            'e_student_diploma',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
	}
	
    public function safeDown()
    {
        $this->dropColumn('e_student_diploma', '_department');
        $this->dropColumn('e_student_diploma', 'department');
		$this->dropForeignKey('fk_e_department_e_student_diploma_fkey','e_student_diploma');
	}
}
