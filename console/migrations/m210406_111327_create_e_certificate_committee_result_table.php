<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_certificate_committee_result}}`.
 */
class m210406_111327_create_e_certificate_committee_result_table extends Migration
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
        $description = 'DAK natijalari';

        $this->createTable(
            '{{%e_certificate_committee_result}}',
            [
                'id' => $this->primaryKey(),
                '_certificate_committee' => $this->integer()->notNull(),
                '_education_type' => $this->string(64)->notNull(),
                '_specialty' => $this->integer()->notNull(),
                '_faculty' => $this->integer()->notNull(),
                '_department' => $this->integer()->notNull(),
                '_student' => $this->integer()->notNull(),
                '_graduate_work' => $this->integer()->notNull(),
                '_group' => $this->integer()->notNull(),
                '_education_year' => $this->string(64)->notNull(),
                '_translations' => 'jsonb',
                'order_number' => $this->string(32)->notNull(),
                'order_date' => $this->date()->notNull(),
                'grade' => $this->integer()->notNull(),
                'active' => $this->boolean()->defaultValue(true),
                'updated_at' => $this->dateTime()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
            ],
            $tableOptions
        );

        $this->addCommentOnTable('e_certificate_committee_result', $description);

        $this->addForeignKey(
            'fk_e_certificate_committee_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_certificate_committee',
            'e_certificate_committee',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_department_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_faculty',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_department_e_certificate_committee_result_fkey2',
            'e_certificate_committee_result',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_specialty',
            'e_specialty',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_graduate_qualifying_work_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_graduate_work',
            'e_graduate_qualifying_work',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_education_type_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_certificate_committee_result_fkey',
            'e_certificate_committee_result',
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
        $this->dropTable('{{%e_certificate_committee_result}}');
    }
}
