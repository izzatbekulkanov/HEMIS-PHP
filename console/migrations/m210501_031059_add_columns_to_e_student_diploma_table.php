<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_student_diploma}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_university}}`
 * - `{{%e_department}}`
 * - `{{%h_education_type}}`
 * - `{{%h_education_form}}`
 * - `{{%e_specialty}}`
 * - `{{%e_qualification}}`
 * - `{{%e_group}}`
 * - `{{%e_student}}`
 * - `{{%h_education_year}}`
 */
class m210501_031059_add_columns_to_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_student_diploma}}', '_university', $this->integer());
        $this->addColumn('{{%e_student_diploma}}', 'university_name', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', '_education_type', $this->string(64));
        $this->addColumn('{{%e_student_diploma}}', 'education_type_name', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', '_education_form', $this->string(64));
        $this->addColumn('{{%e_student_diploma}}', 'education_form_name', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'specialty_code', $this->string(64));
        $this->addColumn('{{%e_student_diploma}}', '_qualification', $this->integer());
        $this->addColumn('{{%e_student_diploma}}', 'qualification_name', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', '_group', $this->integer());
        $this->addColumn('{{%e_student_diploma}}', 'group_name', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'student_birthday', $this->date());
        $this->addColumn('{{%e_student_diploma}}', '_education_year', $this->string(64));
        $this->addColumn('{{%e_student_diploma}}', 'education_year_name', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'diploma_category', $this->string(64));
        $this->addColumn('{{%e_student_diploma}}', 'order_date', $this->date());
        $this->addColumn('{{%e_student_diploma}}', 'rector_fullname', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'given_city', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'post_address', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'education_language', $this->string(64));
        $this->addColumn('{{%e_student_diploma}}', 'education_period', $this->string(30));
        $this->addColumn('{{%e_student_diploma}}', 'last_education', $this->string());
        $this->addColumn('{{%e_student_diploma}}', 'marking_system', $this->string());
        $this->addColumn('{{%e_student_diploma}}', 'university_accreditation', $this->string());
        $this->addColumn('{{%e_student_diploma}}', 'diploma_link', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'suplement_link', $this->string(256));
        $this->addColumn('{{%e_student_diploma}}', 'diploma_status', $this->string(20));

        // creates index for column `_university`
        $this->createIndex(
            '{{%idx-e_student_diploma-_university}}',
            '{{%e_student_diploma}}',
            '_university'
        );

        // add foreign key for table `{{%e_university}}`
        $this->addForeignKey(
            '{{%fk-e_student_diploma-_university}}',
            '{{%e_student_diploma}}',
            '_university',
            '{{%e_university}}',
            'id',
            'CASCADE'
        );

        // creates index for column `_education_type`
        $this->createIndex(
            '{{%idx-e_student_diploma-_education_type}}',
            '{{%e_student_diploma}}',
            '_education_type'
        );

        // add foreign key for table `{{%h_education_type}}`
        $this->addForeignKey(
            '{{%fk-e_student_diploma-_education_type}}',
            '{{%e_student_diploma}}',
            '_education_type',
            '{{%h_education_type}}',
            'code',
            'CASCADE'
        );

        // creates index for column `_education_form`
        $this->createIndex(
            '{{%idx-e_student_diploma-_education_form}}',
            '{{%e_student_diploma}}',
            '_education_form'
        );

        // add foreign key for table `{{%h_education_form}}`
        $this->addForeignKey(
            '{{%fk-e_student_diploma-_education_form}}',
            '{{%e_student_diploma}}',
            '_education_form',
            '{{%h_education_form}}',
            'code',
            'CASCADE'
        );

        // creates index for column `_qualification`
        $this->createIndex(
            '{{%idx-e_student_diploma-_qualification}}',
            '{{%e_student_diploma}}',
            '_qualification'
        );

        // add foreign key for table `{{%e_qualification}}`
        $this->addForeignKey(
            '{{%fk-e_student_diploma-_qualification}}',
            '{{%e_student_diploma}}',
            '_qualification',
            '{{%e_qualification}}',
            'id',
            'CASCADE'
        );

        // creates index for column `_group`
        $this->createIndex(
            '{{%idx-e_student_diploma-_group}}',
            '{{%e_student_diploma}}',
            '_group'
        );

        // add foreign key for table `{{%e_group}}`
        $this->addForeignKey(
            '{{%fk-e_student_diploma-_group}}',
            '{{%e_student_diploma}}',
            '_group',
            '{{%e_group}}',
            'id',
            'CASCADE'
        );

        // creates index for column `_education_year`
        $this->createIndex(
            '{{%idx-e_student_diploma-_education_year}}',
            '{{%e_student_diploma}}',
            '_education_year'
        );

        // add foreign key for table `{{%h_education_year}}`
        $this->addForeignKey(
            '{{%fk-e_student_diploma-_education_year}}',
            '{{%e_student_diploma}}',
            '_education_year',
            '{{%h_education_year}}',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_university}}`
        $this->dropForeignKey(
            '{{%fk-e_student_diploma-_university}}',
            '{{%e_student_diploma}}'
        );

        // drops index for column `_university`
        $this->dropIndex(
            '{{%idx-e_student_diploma-_university}}',
            '{{%e_student_diploma}}'
        );

        // drops foreign key for table `{{%h_education_type}}`
        $this->dropForeignKey(
            '{{%fk-e_student_diploma-_education_type}}',
            '{{%e_student_diploma}}'
        );

        // drops index for column `_education_type`
        $this->dropIndex(
            '{{%idx-e_student_diploma-_education_type}}',
            '{{%e_student_diploma}}'
        );

        // drops foreign key for table `{{%h_education_form}}`
        $this->dropForeignKey(
            '{{%fk-e_student_diploma-_education_form}}',
            '{{%e_student_diploma}}'
        );

        // drops index for column `_education_form`
        $this->dropIndex(
            '{{%idx-e_student_diploma-_education_form}}',
            '{{%e_student_diploma}}'
        );

        // drops foreign key for table `{{%e_qualification}}`
        $this->dropForeignKey(
            '{{%fk-e_student_diploma-_qualification}}',
            '{{%e_student_diploma}}'
        );

        // drops index for column `_qualification`
        $this->dropIndex(
            '{{%idx-e_student_diploma-_qualification}}',
            '{{%e_student_diploma}}'
        );

        // drops foreign key for table `{{%e_group}}`
        $this->dropForeignKey(
            '{{%fk-e_student_diploma-_group}}',
            '{{%e_student_diploma}}'
        );

        // drops index for column `_group`
        $this->dropIndex(
            '{{%idx-e_student_diploma-_group}}',
            '{{%e_student_diploma}}'
        );

        // drops foreign key for table `{{%h_education_year}}`
        $this->dropForeignKey(
            '{{%fk-e_student_diploma-_education_year}}',
            '{{%e_student_diploma}}'
        );

        // drops index for column `_education_year`
        $this->dropIndex(
            '{{%idx-e_student_diploma-_education_year}}',
            '{{%e_student_diploma}}'
        );

        $this->dropColumn('{{%e_student_diploma}}', '_university');
        $this->dropColumn('{{%e_student_diploma}}', 'university_name');
        $this->dropColumn('{{%e_student_diploma}}', '_education_type');
        $this->dropColumn('{{%e_student_diploma}}', 'education_type_name');
        $this->dropColumn('{{%e_student_diploma}}', '_education_form');
        $this->dropColumn('{{%e_student_diploma}}', 'education_form_name');
        $this->dropColumn('{{%e_student_diploma}}', 'specialty_code');
        $this->dropColumn('{{%e_student_diploma}}', '_qualification');
        $this->dropColumn('{{%e_student_diploma}}', 'qualification_name');
        $this->dropColumn('{{%e_student_diploma}}', '_group');
        $this->dropColumn('{{%e_student_diploma}}', 'group_name');
        $this->dropColumn('{{%e_student_diploma}}', 'student_birthday');
        $this->dropColumn('{{%e_student_diploma}}', '_education_year');
        $this->dropColumn('{{%e_student_diploma}}', 'education_year_name');
        $this->dropColumn('{{%e_student_diploma}}', 'diploma_category');
        $this->dropColumn('{{%e_student_diploma}}', 'order_date');
        $this->dropColumn('{{%e_student_diploma}}', 'rector_fullname');
        $this->dropColumn('{{%e_student_diploma}}', 'given_city');
        $this->dropColumn('{{%e_student_diploma}}', 'post_address');
        $this->dropColumn('{{%e_student_diploma}}', 'education_language');
        $this->dropColumn('{{%e_student_diploma}}', 'education_period');
        $this->dropColumn('{{%e_student_diploma}}', 'last_education');
        $this->dropColumn('{{%e_student_diploma}}', 'marking_system');
        $this->dropColumn('{{%e_student_diploma}}', 'university_accreditation');
        $this->dropColumn('{{%e_student_diploma}}', 'diploma_link');
        $this->dropColumn('{{%e_student_diploma}}', 'suplement_link');
        $this->dropColumn('{{%e_student_diploma}}', 'diploma_status');
    }
}
