<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_certificate_committee}}`.
 */
class m210405_180911_create_e_certificate_committee_table extends Migration
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
        $description = 'Davlat attestatsiya komissiyasi';

        $this->createTable('{{%e_certificate_committee}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            '_specialty' => $this->integer()->notNull(),
            '_faculty' => $this->integer()->notNull(),
            '_department' => $this->integer()->notNull(),
            '_education_year' => $this->string(4)->notNull(),
            '_translations' => 'jsonb',
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('e_certificate_committee', $description);
        $this->addForeignKey(
            'fk_h_education_type_e_certificate_committee_fkey',
            'e_certificate_committee',
            '_education_type',
            'h_education_form',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_certificate_committee_fkey',
            'e_certificate_committee',
            '_specialty',
            'e_specialty',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_department_e_certificate_committee_fkey',
            'e_certificate_committee',
            '_faculty',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_department_e_certificate_committee_fkey2',
            'e_certificate_committee',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_certificate_committee_fkey',
            'e_certificate_committee',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_certificate_committee}}');
    }
}
