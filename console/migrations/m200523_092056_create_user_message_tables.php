<?php

use yii\db\Migration;

/**
 * Class m200523_092056_create_user_message_tables
 */
class m200523_092056_create_user_message_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('e_admin_message', [
            'id' => $this->primaryKey(),
            '_forwarded' => $this->integer(),
            '_replied' => $this->integer(),
            '_sender' => $this->integer(),
            '_item' => $this->integer(),
            '_recipients' => 'jsonb',
            'sender_data' => 'jsonb',
            'recipient_data' => 'jsonb',
            'title' => $this->string(256),
            'message' => $this->text(),
            'files' => 'jsonb',
            'status' => $this->string(16)->defaultValue('draft'),
            'status_bd' => $this->string(16),
            'r_count' => $this->integer(5),
            'send_on' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_message_forwarded',
            'e_admin_message',
            '_forwarded',
            'e_admin_message',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_replied',
            'e_admin_message',
            '_replied',
            'e_admin_message',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_sender',
            'e_admin_message',
            '_sender',
            'e_admin',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->createTable('e_admin_message_item', [
            'id' => $this->primaryKey(),
            '_message' => $this->integer()->notNull(),
            '_sender' => $this->integer()->notNull(),
            '_recipient' => $this->integer(),
            '_folder' => $this->integer(),
            '_label' => $this->integer(),
            'type' => $this->string(16)->defaultValue('inbox'),
            'opened' => $this->boolean()->defaultValue(false),
            'deleted' => $this->boolean()->defaultValue(false),
            'starred' => $this->boolean()->defaultValue(false),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'opened_at' => $this->dateTime(),
            'deleted_at' => $this->dateTime(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_message_item_message',
            'e_admin_message_item',
            '_message',
            'e_admin_message',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_item_recipient',
            'e_admin_message_item',
            '_recipient',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_item_sender',
            'e_admin_message_item',
            '_sender',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_item',
            'e_admin_message',
            '_item',
            'e_admin_message_item',
            'id',
            'SET NULL',
            'CASCADE'
        );


        $this->createTable('e_admin_message_folder', [
            'id' => $this->primaryKey(),
            'name' => $this->string(32),
            'position' => $this->integer(3),
            'type' => $this->string(16),
            '_recipient' => $this->integer()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_message_item_folder',
            'e_admin_message_item',
            '_folder',
            'e_admin_message_folder',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_item_label',
            'e_admin_message_item',
            '_label',
            'e_admin_message_folder',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_admin_message_item', 'e_admin_message');
        $this->dropTable('e_admin_message_item');
        $this->dropTable('e_admin_message');
        $this->dropTable('e_admin_message_folder');
    }

}
