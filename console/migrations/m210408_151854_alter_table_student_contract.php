<?php

use yii\db\Migration;

/**
 * Class m210408_151854_alter_table_student_contract
 */
class m210408_151854_alter_table_student_contract extends Migration
{
    
    public function safeUp()
    {
		$this->addColumn('e_student_contract', 'filename', 'jsonb');
	}

    public function safeDown()
    {
		$this->dropColumn('e_student_contract', 'filename');
	}
}
