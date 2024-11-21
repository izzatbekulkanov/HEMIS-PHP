<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_academic_information_data}}`.
 */
class m210807_201923_create_e_academic_information_data_table extends Migration
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
        $description = 'Akademik ma`lumotnoma ma`lumotlari (transkript emas)';

        $this->createTable('e_academic_information_data', [
            'id' => $this->primaryKey(),
            '_specialty'=>$this->integer()->notNull(),
            '_student_meta'=>$this->integer()->notNull(),
            '_student'=>$this->integer()->notNull(),
            '_group'=>$this->integer(),
            '_semester'=>$this->string(64),
            '_university'=>$this->integer(),
            '_department'=>$this->integer()->notNull(),
            '_decree'=>$this->integer(),
            '_education_year'=>$this->string(64),
            '_education_type'=>$this->string(64),
            '_education_form'=>$this->string(64),
            '_curriculum'=>$this->integer(),
            'first_name'=>$this->string(100),
            'second_name'=>$this->string(100),
            'third_name'=>$this->string(100),
            'student_birthday'=>$this->date(),
            'group_name'=>$this->string(100),
            'blank_number'=>$this->string(20),
            'register_number'=>$this->string(30),
            'register_date'=>$this->date(),
            'given_city'=>$this->string(255),
            'semester_name'=>$this->string(255),
            'university_name'=>$this->string(255),
            'rector_fullname' => $this->string(255),
            'dean_fullname' => $this->string(255),
            'secretary_fullname' => $this->string(255),
            'faculty_name' => $this->string(255),
            'continue_start_date'=>$this->date(),
            'continue_end_date'=>$this->date(),
            'moved_hei_name'=>$this->string(1000),
            'studied_start_date'=>$this->date(),
            'studied_end_date'=>$this->date(),
            'specialty_name'=>$this->string(255),
            'specialty_code'=>$this->string(100),
            'accumulated_points'=>$this->decimal(5, 1),
            'passing_score'=>$this->decimal(5, 1),
            'last_education'=>$this->string(255),
            'expulsion_decree_reason'=>$this->string(255),
            'expulsion_decree_number'=>$this->string(255),
            'expulsion_decree_date'=>$this->date(),
            'education_form_name'=>$this->string(255),
            'academic_data_status' => $this->string(64),
            'subjects_count'=>$this->integer(),
            '_translations' => 'jsonb',
            'filename' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_specialty_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_student_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_student',
            'e_student',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_student_meta_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_student_meta',
            'e_student_meta',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_group_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_group',
            'e_group',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_university_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_university',
            'e_university',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_e_department_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_department',
            'e_department',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_e_decree_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_type_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_academic_information_data_fkey',
            'e_academic_information_data',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT'
        );

        $this->createIndex('e_academic_information_data_unique',
            'e_academic_information_data',
            ['_student', '_student_meta'],
            true);
        $this->createIndex('e_academic_information_data_blank_number_unique',
            'e_academic_information_data',
            ['blank_number'],
            true);
        $this->createIndex('e_academic_information_data_register_number_unique',
            'e_academic_information_data',
            ['register_number'],
            true);
        $this->addCommentOnTable('e_academic_information_data', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_academic_information_data_subject');
    }
}
