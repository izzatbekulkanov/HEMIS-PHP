<?php

use yii\db\Migration;

/**
 * Class m200610_061056_alter_table_employee
 */
class m200610_061056_alter_table_employee extends Migration
{

    public function safeUp()
    {
        $this->addColumn('e_employee', 'home_address', $this->string(512));
        $this->addColumn('e_employee', '_citizenship', $this->string(64)->null());

        $this->addForeignKey(
            'fk_employee_citizenship',
            'e_employee',
            '_citizenship',
            'h_citizenship_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_employee', 'home_address');
        $this->dropColumn('e_employee', '_citizenship');
    }

}
