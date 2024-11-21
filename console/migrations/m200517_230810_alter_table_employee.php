<?php

use yii\db\Migration;

/**
 * Class m200517_230810_alter_table_employee
 */
class m200517_230810_alter_table_employee extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_employee', '_admin', $this->integer());
        $this->addColumn('e_employee', 'telephone', $this->string(32));
        $this->addColumn('e_employee', 'email', $this->string(64));
        $this->addForeignKey(
            'fk_employee_admin',
            'e_employee',
            '_admin',
            'e_admin',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addColumn('e_admin', '_employee', $this->integer());
        $this->addForeignKey(
            'fk_admin_employee',
            'e_admin',
            '_employee',
            'e_employee',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_employee', 'telephone');
        $this->dropColumn('e_employee', 'email');
        $this->dropColumn('e_employee', '_admin');
        $this->dropColumn('e_admin', '_employee');
    }
}
