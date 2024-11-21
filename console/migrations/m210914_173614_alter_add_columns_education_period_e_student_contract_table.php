<?php

use yii\db\Migration;

/**
 * Class m210914_173614_alter_add_columns_education_period_e_student_contract_table
 */
class m210914_173614_alter_add_columns_education_period_e_student_contract_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', 'education_period', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', 'education_period');
    }
}
