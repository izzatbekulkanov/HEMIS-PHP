<?php

use yii\db\Migration;

/**
 * Class m200225_081428_create_table_login
 */
class m200225_081428_create_table_login extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('e_system_login', [
            'id' => $this->primaryKey(),
            'login' => $this->string(32)->notNull(),
            'status' => $this->string(16),
            'type' => $this->string(16),
            'ip' => $this->string(16),
            'query' => $this->string(256),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_system_login_status', 'e_system_login', 'status');
    }

    public function down()
    {
        $this->dropTable('e_system_login');
    }
}
