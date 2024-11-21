<?php

use yii\db\Migration;

/**
 * Class m211006_183548_alter_table_e_employee_academic_degree
 */
class m211006_183548_alter_table_e_employee_academic_degree extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_employee_academic_degree', '_country', $this->string(64)->null());
        $this->addColumn('e_employee_academic_degree', 'university', $this->string(512)->null());

        $this->addForeignKey(
            'fk_e_employee_academic_degree_country',
            'e_employee_academic_degree',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_employee_academic_degree', '_country');
        $this->dropColumn('e_employee_academic_degree', 'university');
    }

}
