<?php

use yii\db\Migration;
use common\models\finance\EStudentContract;

/**
 * Class m210420_081942_alter_table_student_contract_datenull
 */
class m210420_081942_alter_table_student_contract_datenull extends Migration
{
    public function safeUp()
    {
        $this->alterColumn(EStudentContract::tableName(), 'date', $this->date()->null());
    }
	
	public function safeDown()
    {
        $this->alterColumn(EStudentContract::tableName(), 'date', $this->date()->notNull());
    }
}
