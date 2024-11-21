<?php

use yii\db\Migration;

/**
 * Class m210405_131054_create_table_student_gpa
 */
class m210405_131054_create_table_student_gpa extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning GPA ballari';

        $this->createTable('e_student_gpa', [
            'id' => $this->primaryKey(),
            '_student' => $this->integer()->notNull(),
            '_student_meta' => $this->integer()->notNull(),
            '_department' => $this->integer()->notNull(),
            '_curriculum' => $this->integer()->notNull(),
            '_group' => $this->integer()->notNull(),

            '_education_type' => $this->string(64)->notNull(),
            '_education_year' => $this->string(64)->notNull(),
            '_education_form' => $this->string(64)->notNull(),
            '_level' => $this->string(64)->notNull(),
            'data' => 'jsonb',
            'subjects' => $this->integer(3)->defaultValue(0),
            'credit_sum' => $this->decimal(6, 1)->defaultValue(0),
            'gpa' => $this->decimal(6, 1)->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_student_gpa_student',
            'e_student_gpa',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_student_meta',
            'e_student_gpa',
            '_student_meta',
            'e_student_meta',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_department',
            'e_student_gpa',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_curriculum',
            'e_student_gpa',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_group',
            'e_student_gpa',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_education_year',
            'e_student_gpa',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_education_form',
            'e_student_gpa',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_education_type',
            'e_student_gpa',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_gpa_education_level',
            'e_student_gpa',
            '_level',
            'h_course',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->createIndex('idx_e_student_gpa_student_education_year', 'e_student_gpa', ['_student', '_education_year'], true);
        $this->addCommentOnTable('e_student_gpa', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_gpa');
    }
}
