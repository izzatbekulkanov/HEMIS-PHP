<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_contract_invoice}}`.
 */
class m210925_101559_create_e_student_contract_invoice_table extends Migration
{
   public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning shartnomalari uchun hisob fakturalar';

        $this->createTable('e_student_contract_invoice', [
            'id' => $this->primaryKey(),
            '_student_contract'=>$this->integer(),
            '_education_year'=>$this->string(64),
            '_student'=>$this->integer()->notNull(),
            '_department'=>$this->integer()->notNull(),
            '_specialty'=>$this->integer()->notNull(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            '_level'=>$this->string(64)->notNull(),
            '_curriculum'=>$this->integer()->notNull(),
            '_group'=>$this->integer()->notNull(),
            'invoice_number' => $this->string(255)->unique(),
            'invoice_date' => $this->date()->notNull(),
            'invoice_summa'=>$this->money()->notNull(),
            'invoice_status' => $this->string(64),
            'filename' => 'jsonb',
            '_translations' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_student_contract_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_student_contract',
            'e_student_contract',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_student_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_student',
            'e_student',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_department_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_department',
            'e_department',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_type_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_course_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_level',
            'h_course',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_group_e_student_contract_invoice_fkey',
            'e_student_contract_invoice',
            '_group',
            'e_group',
            'id',
            'RESTRICT'
        );
        $this->addCommentOnTable('e_student_contract_invoice', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_contract_invoice');
    }
}
