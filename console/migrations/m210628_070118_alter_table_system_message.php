<?php

use backend\models\FormUploadTrans;
use yii\db\Migration;
use yii\helpers\Console;

/**
 * Class m210628_070118_alter_table_system_message
 */
class m210628_070118_alter_table_system_message extends Migration
{
    public function up()
    {
        $translations = Yii::$app->i18n->translations;
        Yii::$app->i18n->translations = [];

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->dropTable('e_system_message_translation');
        $this->dropTable('e_system_message');

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

    }
}
