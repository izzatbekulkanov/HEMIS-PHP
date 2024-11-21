<?php

use yii\db\Migration;

/**
 * Class m210329_164446_alter_table_increased_contract_coefficient
 */
class m210329_164446_alter_table_increased_contract_coefficient extends Migration
{
    public function safeUp()
    {
		$this->addColumn('e_increased_contract_coefficient', '_education_type', $this->string(64)->null());
	}

    public function safeDown()
    {
		$this->dropColumn('e_increased_contract_coefficient', '_education_type');
	}
}
