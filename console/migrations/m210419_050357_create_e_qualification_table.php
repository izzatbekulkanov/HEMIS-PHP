<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_qualification}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_specialty}}`
 */
class m210419_050357_create_e_qualification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Kvalifikatsiyalar';
        $this->createTable('{{%e_qualification}}', [
            'id' => $this->primaryKey(),
            '_specialty' => $this->integer()->notNull(),
            'name' => $this->string(128)->notNull(),
            'description' => $this->string(1024)->notNull(),
            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'json',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('{{%e_qualification}}', $description);

        // creates index for column `_specialty`
        $this->createIndex(
            '{{%idx-e_qualification-_specialty}}',
            '{{%e_qualification}}',
            '_specialty'
        );

        // add foreign key for table `{{%e_specialty}}`
        $this->addForeignKey(
            '{{%fk-e_qualification-_specialty}}',
            '{{%e_qualification}}',
            '_specialty',
            '{{%e_specialty}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_specialty}}`
        $this->dropForeignKey(
            '{{%fk-e_qualification-_specialty}}',
            '{{%e_qualification}}'
        );

        // drops index for column `_specialty`
        $this->dropIndex(
            '{{%idx-e_qualification-_specialty}}',
            '{{%e_qualification}}'
        );

        $this->dropTable('{{%e_qualification}}');
    }
}
