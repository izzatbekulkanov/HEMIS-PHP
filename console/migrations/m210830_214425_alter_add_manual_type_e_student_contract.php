<?php

use yii\db\Migration;

/**
 * Class m210830_214425_alter_add_manual_type_e_student_contract
 */
class m210830_214425_alter_add_manual_type_e_student_contract extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', '_manual_type', $this->string(64)->defaultValue('11'));
        $this->addColumn('e_paid_contract_fee', '_manual_type', $this->string(64)->defaultValue('11'));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', '_manual_type');
        $this->dropColumn('e_paid_contract_fee', '_manual_type');
    }
}
