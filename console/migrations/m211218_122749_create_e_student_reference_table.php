<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_reference}}`.
 */
class m211218_122749_create_e_student_reference_table extends Migration
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
        $description = 'Talabalarning o`qish joyidan ma`lumotnomalari';

        $this->createTable('e_student_reference', [
            'id' => $this->primaryKey(),
            '_student_meta'=>$this->integer()->notNull(),
            '_student'=>$this->integer()->notNull(),
            '_department'=>$this->integer()->notNull(),
            '_specialty'=>$this->integer()->notNull(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
            '_curriculum'=>$this->integer(),
            '_semester'=>$this->string(64)->notNull(),
            '_level'=>$this->string(64)->notNull(),
            'university_name'=>$this->string(255),
            'first_name'=>$this->string(100),
            'second_name'=>$this->string(100),
            'third_name'=>$this->string(100),
            'passport_pin'=>$this->string(20),
            'birth_date'=>$this->date(),
            'year_of_enter'=>$this->integer()->notNull(),
            '_citizenship'=>$this->string(64)->notNull(),
            '_payment_form'=>$this->string(64)->notNull(),
            'reference_number' => $this->string(255)->unique(),
            'reference_date' => $this->date()->notNull(),
            'hash' => $this->string(36)->unique(),
            'filename' => 'jsonb',
            '_translations' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_student_meta_e_student_reference_fkey',
            'e_student_reference',
            '_student_meta',
            'e_student_meta',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_student_e_student_reference_fkey',
            'e_student_reference',
            '_student',
            'e_student',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_department_e_student_reference_fkey',
            'e_student_reference',
            '_department',
            'e_department',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_student_reference_fkey',
            'e_student_reference',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_type_e_student_reference_fkey',
            'e_student_reference',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_student_reference_fkey',
            'e_student_reference',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_student_reference_fkey',
            'e_student_reference',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_student_reference_fkey',
            'e_student_reference',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_course_e_student_reference_fkey',
            'e_student_reference',
            '_level',
            'h_course',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_citizenship_type_e_student_reference_fkey',
            'e_student_reference',
            '_citizenship',
            'h_citizenship_type',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_payment_forme_student_reference_fkey',
            'e_student_reference',
            '_payment_form',
            'h_payment_form',
            'code',
            'CASCADE'
        );
        $this->createIndex('e_student_reference_data_unique',
            'e_student_reference',
            ['_student', '_specialty', '_education_year', '_semester', '_education_form'],
            true);
        $this->addCommentOnTable('e_student_reference', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student_reference');
    }
}
