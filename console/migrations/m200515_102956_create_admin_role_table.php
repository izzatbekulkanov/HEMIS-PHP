<?php

use yii\db\Migration;

class m200515_102956_create_admin_role_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->addColumn('e_admin_role', 'position', $this->integer()->defaultValue(0));
        $this->alterColumn('e_admin', '_role', $this->integer()->null());

        $this->createTable('e_admin_roles', [
            '_admin' => $this->integer()->notNull(),
            '_role' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_roles_admin',
            'e_admin_roles',
            '_admin',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_roles_role',
            'e_admin_roles',
            '_role',
            'e_admin_role',
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
        $this->dropColumn('e_admin_role', 'position');
        $this->alterColumn('e_admin', '_role', $this->integer()->notNull());

        $this->dropForeignKey('fk_admin_roles_role', 'e_admin_roles');
        $this->dropForeignKey('fk_admin_roles_admin', 'e_admin_roles');
        $this->dropTable('e_admin_roles');
    }
}
