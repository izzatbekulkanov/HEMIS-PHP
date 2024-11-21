<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subject}}`.
 */
class m200329_200159_create_e_subject_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'OTMdagi fanlar ro`yxati';

        $this->createTable('e_subject', [
            'id' => $this->primaryKey(),
            'code' => $this->string(64)->notNull()->unique(),
            'name' => $this->string(256)->notNull(),
            '_subject_group' => $this->string(64)->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $classifier = 'h_subject_group';

        $this->createTable($classifier, [
            'code' => $this->string(64)->notNull()->unique(),
            'name' => $this->string(256)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            '_parent' => $this->string(64)->null(),
            '_translations' => 'jsonb',
            '_options' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey("pk_$classifier", $classifier, 'code');
        $this->createIndex("idx_{$classifier}_position", $classifier, 'position');
        $this->createIndex("idx_{$classifier}_active", $classifier, 'active');

        $this->addForeignKey(
            "fk_{$classifier}_parent_code",
            $classifier,
            '_parent',
            $classifier,
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_subject_group_e_subject_fkey',
            'e_subject',
            '_subject_group',
            'h_subject_group',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_education_type_e_subject_fkey',
            'e_subject',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
        $this->addCommentOnTable('e_subject', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_subject');
    }
}
