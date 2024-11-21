<?php

use yii\db\Migration;

/**
 * Class m200728_172925_alter_table_employee_meta
 */
class m200728_172925_alter_table_employee_meta extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey(
            'fk_e_employee_employee_meta_fkey',
            'e_employee_meta',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }
    public function safeDown()
    {
        $this->dropForeignKey('fk_e_employee_employee_meta_fkey', 'e_employee_meta');
    }
}
