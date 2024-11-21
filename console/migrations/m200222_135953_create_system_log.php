<?php

use yii\db\Migration;

/**
 * Class m200222_135953_create_system_log
 */
class m200222_135953_create_system_log extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('e_system_log', [
            'id' => $this->primaryKey(),
            '_admin' => $this->integer()->notNull(),
            'admin_name' => $this->string()->notNull(),
            'action' => $this->string(255),
            'type' => $this->string(255),
            'message' => $this->string(1024),
            'get' => 'jsonb',
            'post' => 'jsonb',
            'ip' => $this->string(16),
            'x_ip' => $this->string(16),
            'query' => $this->string(256),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_system_log_admin',
            'e_system_log',
            '_admin',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('e_system_log');
    }
}
