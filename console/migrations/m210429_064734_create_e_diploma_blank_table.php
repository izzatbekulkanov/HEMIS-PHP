<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_diploma_blank}}`.
 */
class m210429_064734_create_e_diploma_blank_table extends Migration
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
        $description = 'Diplom blanklari';
        $this->createTable('{{%e_diploma_blank}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(64)->notNull(),
            'category' => $this->string(64)->notNull(),
            'number' => $this->integer()->notNull(),
            'reason' => $this->string(),
            'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            '_uid' => $this->string()->unique(),
            '_sync' => $this->boolean()->defaultValue(false),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('{{%e_diploma_blank}}', $description);

        $this->createIndex('e_diploma_blank_number_idx', '{{%e_diploma_blank}}', 'number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('e_diploma_blank_number_idx', '{{%e_diploma_blank}}');
        $this->dropTable('{{%e_diploma_blank}}');
    }
}
