<?php

use yii\db\Migration;

/**
 * Class m210329_101725_alter_table_contract_price
 */
class m210329_101725_alter_table_contract_price extends Migration
{
    public function safeUp()
    {
		$this->alterColumn('e_contract_price', '_student_type',$this->string(64)->null());
		$this->alterColumn('e_contract_price', 'summa',$this->money()->null());
    }

    public function safeDown()
    {
		$this->alterColumn('e_contract_price', '_student_type', $this->string(64)->notNull());
		$this->alterColumn('e_contract_price', 'summa', $this->money()->notNull());
    }
}
