<?php

use yii\db\Migration;

/**
 * Class m210329_062238_alter_table_contract_price_increased_coef
 */
class m210329_062238_alter_table_contract_price_increased_coef extends Migration
{
    public function safeUp()
    {
		$this->addColumn('e_contract_price', 'contract_locality', $this->string(64)->null());
		$this->alterColumn('e_increased_contract_coefficient', '_education_year',$this->string(64)->null());
    }

    public function safeDown()
    {
		$this->dropColumn('e_contract_price', 'contract_locality');
		$this->alterColumn('e_increased_contract_coefficient', '_education_year', $this->string(64)->notNull());
    }

}
