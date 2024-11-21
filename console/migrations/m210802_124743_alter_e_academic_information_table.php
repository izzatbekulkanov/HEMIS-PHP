<?php

use yii\db\Migration;

/**
 * Class m210802_124743_alter_e_academic_information_table
 */
class m210802_124743_alter_e_academic_information_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_academic_information', '_university', $this->integer());
        $this->addColumn('e_academic_information', 'university_name', $this->string(255));
        $this->addColumn('e_academic_information', 'department_name', $this->string(255));
        $this->addColumn('e_academic_information', 'student_name', $this->string(255));
        $this->addColumn('e_academic_information', 'student_birthday', $this->date());
        $this->addColumn('e_academic_information', 'group_name', $this->string(255));
        $this->addColumn('e_academic_information', '_curriculum', $this->integer());
        $this->addColumn('e_academic_information', 'curriculum_name', $this->string(255));
        $this->addColumn('e_academic_information', 'student_status', $this->string(255));
        $this->addColumn('e_academic_information', 'education_type_name', $this->string(255));
        $this->addColumn('e_academic_information', 'education_form_name', $this->string(255));
        $this->addColumn('e_academic_information', 'specialty_name', $this->string(255));
        $this->addColumn('e_academic_information', 'specialty_code', $this->string(255));
        $this->addColumn('e_academic_information', 'year_of_entered', $this->string(255));
        $this->addColumn('e_academic_information', 'year_of_graduated', $this->string(255));
        $this->addColumn('e_academic_information', '_semester', $this->string(64));
        $this->addColumn('e_academic_information', 'semester_name', $this->string(255));
        $this->addColumn('e_academic_information', 'subjects_count', $this->integer());
        $this->addColumn('e_academic_information', '_translations', 'jsonb');

        $this->createIndex(
            'idx-e_academic_information-_university',
            'e_academic_information',
            '_university'
        );
        $this->addForeignKey(
            'fk-e_e_academic_information-_university',
            'e_academic_information',
            '_university',
            'e_university',
            'id',
            'RESTRICT'
        );

        $this->createIndex(
            'idx-e_academic_information-_education_type',
            'e_academic_information',
            '_education_type'
        );

        $this->createIndex(
            'idx-e_academic_information-_education_form',
            'e_academic_information',
            '_education_form'
        );

        $this->createIndex(
            'idx-e_academic_information-_group',
            'e_academic_information',
            '_group'
        );

        $this->createIndex(
            'idx-e_academic_information-_curriculum',
            'e_academic_information',
            '_curriculum'
        );
        $this->addForeignKey(
            'fk-e_academic_information-_curriculum',
            'e_academic_information',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT'
        );
        $this->createIndex(
            'idx-e_academic_information-_specialty',
            'e_academic_information',
            '_specialty'
        );

        $this->createIndex(
            'idx-e_academic_information-_department',
            'e_academic_information',
            '_department'
        );

        $this->createIndex('transcript_academic_register_number_unique',
            'e_academic_information',
            ['academic_register_number'],
            true);
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'idx-e_academic_information-_university',
            'e_academic_information'
        );
        $this->dropForeignKey(
            'idx-e_academic_information-_curriculum',
            'e_academic_information'
        );
        $this->dropForeignKey(
            'idx-e_academic_information-_education_type',
            'e_academic_information'
        );
        $this->dropForeignKey(
            'idx-e_academic_information-_education_form',
            'e_academic_information'
        );
        $this->dropForeignKey(
            'idx-e_academic_information-_group',
            'e_academic_information'
        );
        $this->dropForeignKey(
            'idx-e_academic_information-_specialty',
            'e_academic_information'
        );
        $this->dropForeignKey(
            'idx-e_academic_information-_department',
            'e_academic_information'
        );

        $this->dropColumn('e_academic_information', '_university');
        $this->dropColumn('e_academic_information', 'university_name');
        $this->dropColumn('e_academic_information', 'department_name');
        $this->dropColumn('e_academic_information', 'student_name');
        $this->dropColumn('e_academic_information', 'student_birthday');
        $this->dropColumn('e_academic_information', 'group_name');
        $this->dropColumn('e_academic_information', '_curriculum');
        $this->dropColumn('e_academic_information', 'curriculum_name');
        $this->dropColumn('e_academic_information', 'student_status');
        $this->dropColumn('e_academic_information', 'education_type_name');
        $this->dropColumn('e_academic_information', 'education_form_name');
        $this->dropColumn('e_academic_information', 'specialty_name');
        $this->dropColumn('e_academic_information', 'specialty_code');
        $this->dropColumn('e_academic_information', 'year_of_entered');
        $this->dropColumn('e_academic_information', 'year_of_graduated');
        $this->dropColumn('e_academic_information', '_semester');
        $this->dropColumn('e_academic_information', 'semester_name');
    }

}
