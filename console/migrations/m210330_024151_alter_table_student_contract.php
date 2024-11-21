<?php

use yii\db\Migration;

/**
 * Class m210330_024151_alter_table_student_contract
 */
class m210330_024151_alter_table_student_contract extends Migration
{
	public function safeUp()
    {
		$this->addColumn('e_student_contract', '_contract_type', $this->string(64)->defaultValue('11'));
	}

    public function safeDown()
    {
		$this->dropColumn('e_student_contract', '_contract_type');
	}
}
