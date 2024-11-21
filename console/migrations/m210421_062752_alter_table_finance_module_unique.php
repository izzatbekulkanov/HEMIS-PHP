<?php

use yii\db\Migration;

/**
 * Class m210421_062752_alter_table_finance_module_unique
 */
class m210421_062752_alter_table_finance_module_unique extends Migration
{
   public function safeUp()
   {
		$this->createIndex('e_contract_type_unique',
			'e_contract_type',
			['_contract_type'],
			true);
		$this->createIndex('e_increased_contract_coefficient_unique',
			'e_increased_contract_coefficient',
			['_department', '_specialty'],
			true);
   }
   
   public function safeDown()
   {
		$this->dropIndex('e_contract_type_unique', 'e_contract_type');
		$this->dropIndex('e_increased_contract_coefficient_unique', 'e_increased_contract_coefficient');
   }
}
