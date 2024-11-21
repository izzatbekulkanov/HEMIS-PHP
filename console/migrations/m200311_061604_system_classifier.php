<?php

use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m200311_061604_system_classificator
 */
class m200311_061604_system_classifier extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        if ($this->db->schema->getTableSchema('e_system_classifier', true) === null) {
            $this->createTable('e_system_classifier', [
                'id' => $this->primaryKey(),
                'classifier' => $this->string(64)->notNull()->unique(),
                'name' => $this->text(),
                'version' => $this->integer()->defaultValue(0),
                'options' => 'jsonb',
                '_translations' => 'jsonb',
                'position' => $this->integer(3)->defaultValue(0),
                'updated_at' => $this->dateTime()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
            ], $tableOptions);
        }

        SystemClassifier::createClassifiersTables($this);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        SystemClassifier::deleteClassifiersTables($this);

        $this->dropTable('e_system_classifier');
    }

}
