<?php

use yii\db\Migration;

/**
 * Class m211014_105211_add_meta_data_e_student_employment
 */
class m211014_105211_add_meta_data_e_student_employment extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_employment', '_education_year', $this->string(64)->null());
        $this->addColumn('e_student_employment', '_education_type', $this->string(64)->null());
        $this->addColumn('e_student_employment', '_education_form', $this->string(64)->null());
        $this->addColumn('e_student_employment', '_gender', $this->string(64)->null());
        $this->addColumn('e_student_employment', '_department', $this->integer()->null());
        $this->addColumn('e_student_employment', '_specialty', $this->integer()->null());
        $this->addColumn('e_student_employment', '_level', $this->string(64)->null());
        $this->renameColumn(
            'e_student_employment',
            'workplace_compatibility ',
            'workplace_compatibility'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_student_employment_fkey',
            'e_student_employment',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_type_e_student_employment_fkey',
            'e_student_employment',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_student_employment_fkey',
            'e_student_employment',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_gender_e_student_employment_fkey',
            'e_student_employment',
            '_gender',
            'h_gender',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_department_e_student_employment_fkey',
            'e_student_employment',
            '_department',
            'e_department',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_student_employment_fkey',
            'e_student_employment',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_h_course_e_student_employment_fkey',
            'e_student_employment',
            '_level',
            'h_course',
            'code',
            'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_employment', '_education_year');
        $this->dropColumn('e_student_employment', '_education_type');
        $this->dropColumn('e_student_employment', '_education_form');
        $this->dropColumn('e_student_employment', '_gender');
        $this->dropColumn('e_student_employment', '_department');
        $this->dropColumn('e_student_employment', '_specialty');
        $this->dropColumn('e_student_employment', '_level');
        $this->renameColumn(
            'e_student_employment',
            'workplace_compatibility',
            'workplace_compatibility '
        );
    }
}
