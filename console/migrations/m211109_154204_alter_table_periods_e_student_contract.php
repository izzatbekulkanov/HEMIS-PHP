<?php

use yii\db\Migration;

/**
 * Class m211109_154204_alter_table_e_student_contract
 */
class m211109_154204_alter_table_periods_e_student_contract extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', 'start_date', $this->date()->null());
        $this->addColumn('e_student_contract', 'end_date', $this->date()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', 'start_date');
        $this->dropColumn('e_student_contract', 'end_date');
    }
}
