<?php

use yii\db\Migration;

/**
 * Class m200225_141532_create_table_translations
 */
class m100225_141532_create_table_translations extends Migration
{
    public function up()
    {
        $translations = Yii::$app->i18n->translations;
        Yii::$app->i18n->translations = [];

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('e_system_config', [
            'path' => $this->string(64)->unique(),
            'value' => $this->text(),
        ], $tableOptions);

        $this->createTable('e_system_message', [
            'id' => $this->primaryKey(),
            'category' => $this->string(32),
            'message' => $this->text()->notNull(),
        ], $tableOptions);

        $this->createTable('e_system_message_translation', [
            'id' => $this->integer()->notNull(),
            'language' => $this->string(16)->notNull(),
            'translation' => $this->text(),
        ], $tableOptions);

        $this->addPrimaryKey('pk_e_system_message_translation_id_language', '{{%e_system_message_translation}}', ['id', 'language']);

        $onUpdateConstraint = 'RESTRICT';

        $this->addForeignKey('fk_system_message_translation_system_message', 'e_system_message_translation', 'id', 'e_system_message', 'id', 'CASCADE', $onUpdateConstraint);
        $this->createIndex('idx_system_message_category', 'e_system_message', 'category');
        $this->createIndex('idx_system_message_translation_language', 'e_system_message_translation', 'language');

        Yii::$app->i18n->translations = $translations;
    }

    public function down()
    {
        $this->dropForeignKey('fk_system_message_translation_system_message', 'e_system_message_translation');
        $this->dropTable('e_system_message_translation');
        $this->dropTable('e_system_message');
        $this->dropTable('e_system_config');
    }
}
