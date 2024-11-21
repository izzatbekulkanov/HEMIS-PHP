<?php

use yii\db\Migration;

class m130524_201442_create_admin_tables extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('e_admin', [
            'id' => $this->primaryKey(),
            'login' => $this->string()->notNull()->unique(),
            '_role' => $this->integer()->notNull(),
            'password' => $this->string()->notNull(),
            'email' => $this->string(64)->unique(),
            'telephone' => $this->string(32),
            'full_name' => $this->string(32)->notNull(),
            'auth_key' => $this->string(32),
            'access_token' => $this->string(32)->unique(),
            'password_reset_token' => $this->string(32)->unique(),
            'password_reset_date' => $this->dateTime(),
            'image' => 'jsonb',
            'language' => $this->string(32)->notNull()->defaultValue(\common\components\Config::LANGUAGE_UZBEK),
            'status' => $this->string(32)->notNull()->defaultValue(\common\models\system\Admin::STATUS_ENABLE),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createTable('e_admin_role', [
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->notNull()->unique(),
            'name' => $this->string(32)->notNull()->unique(),
            'status' => $this->string(16)->notNull(),
            'parent' => $this->integer(),
            '_options' => 'jsonb',
            '_translations' => 'jsonb',
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_role_admin_role',
            'e_admin',
            '_role',
            'e_admin_role',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_role_parent_admin_role',
            'e_admin_role',
            'parent',
            'e_admin_role',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->createTable('e_admin_resource', [
            'id' => $this->primaryKey(),
            'path' => $this->string(128)->notNull()->unique(),
            'name' => $this->string(256)->notNull()->unique(),
            'group' => $this->string(64)->notNull(),
            'comment' => $this->text(),
            'active' => $this->boolean()->comment('Is Resource is available to use'),
            'login' => $this->boolean()->comment('Should user logged in to access this resource'),
            'skip' => $this->boolean()->comment('Should FilterAccessControl skip this resource'),
            '_options' => 'jsonb',
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createTable('e_admin_role_resource', [
            '_role' => $this->integer(),
            '_resource' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_role_resource_role',
            'e_admin_role_resource',
            '_role',
            'e_admin_role',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_role_resource_resource',
            'e_admin_role_resource',
            '_resource',
            'e_admin_resource',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropForeignKey('fk_admin_role_resource_resource', 'e_admin_role_resource');
        $this->dropForeignKey('fk_admin_role_resource_role', 'e_admin_role_resource');
        $this->dropTable('e_admin_role_resource');
        $this->dropTable('e_admin_resource');

        $this->dropTable('e_admin');
        $this->dropTable('e_admin_role');
    }
}
