<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_admission_quota}}`.
 */
class m210310_112805_create_e_admission_quota_table extends Migration
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
        $description = 'Qabul kvotalari';

        $this->createTable('{{%e_admission_quota}}', [
            'id' => $this->primaryKey(),
            '_specialty' => $this->integer()->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            '_education_form' => $this->string(64)->notNull(),
            '_education_year' => $this->string(64),
            'admission_quota' => $this->integer()->notNull(),
            '_quota_type' => $this->string(64)->notNull(),
            '_translations' => 'jsonb',
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('{{%e_admission_quota}}', $description);

        $this->addForeignKey(
            'fk_h_education_form_e_admission_quota_fkey',
            'e_admission_quota',
            '_education_form',
            'h_education_form',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_type_e_admission_quota_fkey',
            'e_admission_quota',
            '_education_type',
            'h_education_form',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_admission_quota_fkey',
            'e_admission_quota',
            '_specialty',
            'e_specialty',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_admission_quota_fkey',
            'e_admission_quota',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_payment_form_e_admission_quota_fkey',
            'e_admission_quota',
            '_quota_type',
            'h_payment_form',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_admission_quota}}');
    }
}
