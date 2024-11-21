<?php

use yii\db\Migration;

/**
 * Class m210328_073435_alter_tables_student_contract_type
 */
class m210328_073435_alter_tables_student_contract_type extends Migration
{
   public function safeUp()
   {
		$this->addColumn('e_student_contract_type', '_department', $this->integer()->null());
		$this->addColumn('e_student_contract', '_department', $this->integer()->null());
		$this->alterColumn('e_student_contract', 'summa', $this->money()->null());
		$this->addColumn('e_student_contract', '_curriculum', $this->integer()->null());
		$this->addColumn('e_student_contract', '_group', $this->integer()->null());
		
		$this->createIndex('student_contract_type_unique',
			'e_student_contract_type',
			['_specialty', '_student', '_education_year', '_education_form'],
			true);
		$this->createIndex('student_contract_unique',
			'e_student_contract',
			['_specialty', '_student', '_education_year', '_education_form'],
			true);
   }
   
   public function safeDown()
   {
		$this->dropColumn('e_student_contract_type', '_department');
        $this->dropColumn('e_student_contract', '_department');
        $this->dropColumn('e_student_contract', '_curriculum');
        $this->dropColumn('e_student_contract', '_group');
		$this->alterColumn('e_student_contract', 'summa', $this->money()->notNull());
		$this->dropIndex('student_contract_type_unique', 'e_student_contract_type');
		$this->dropIndex('student_contract_unique', 'e_student_contract');
   }
}
