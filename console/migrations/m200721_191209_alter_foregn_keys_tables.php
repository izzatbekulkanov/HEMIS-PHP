<?php

use yii\db\Migration;

/**
 * Class m200721_191209_alter_foregn_keys_tables
 */
class m200721_191209_alter_foregn_keys_tables extends Migration
{
    public function safeUp()
    {
        //e_university
        $this->dropForeignKey('fk_h_ownership','e_university');
        $this->dropForeignKey('fk_h_university_form','e_university');
        $this->alterColumn('e_university', '_ownership', $this->string(64)->null());
        $this->alterColumn('e_university', '_university_form', $this->string(64)->null());
        $this->addForeignKey(
            'fk_h_ownership',
            'e_university',
            '_ownership',
            'h_ownership',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_university_form',
            'e_university',
            '_university_form',
            'h_university_form',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_department
        $this->dropForeignKey('fk_e_university_id','e_department');
        $this->dropForeignKey('e_department_structure_type_fkey','e_department');
        $this->addForeignKey(
                'fk_e_university_id',
                'e_department',
                '_university',
                'e_university',
                'id',
                'RESTRICT',
                'CASCADE'
            );
            $this->addForeignKey(
                'e_department_structure_type_fkey',
                'e_department',
                '_structure_type',
                'h_structure_type',
                'code',
                'RESTRICT',
                'CASCADE'
            );

        //e_specialty
        $this->dropForeignKey('fk_e_department_id_fkey','e_specialty');
        $this->dropForeignKey('fk_s_education_type_fkey','e_specialty');
        $this->addForeignKey(
            'fk_e_department_id_fkey',
            'e_specialty',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_s_education_type_fkey',
            'e_specialty',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_curriculum
        $this->dropForeignKey('fk_c_department_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_education_type_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_education_form_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_specialty_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_marking_system_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_education_year_fkey','e_curriculum');
        $this->alterColumn('e_curriculum', '_marking_system', $this->string(64)->null());
        $this->addForeignKey(
            'fk_c_department_fkey',
            'e_curriculum',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_c_education_type_fkey',
            'e_curriculum',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_c_education_form_fkey',
            'e_curriculum',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_c_specialty_fkey',
            'e_curriculum',
            '_specialty',
            'e_specialty',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_c_marking_system_fkey',
            'e_curriculum',
            '_marking_system',
            'h_marking_system',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_c_education_year_fkey',
            'e_curriculum',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );

        //e_group
        $this->dropForeignKey('fk_e_department_fkey','e_group');
        $this->dropForeignKey('fk_g_education_type_fkey','e_group');
        $this->dropForeignKey('fk_g_education_form_fkey','e_group');
        $this->dropForeignKey('fk_g_specialty_fkey','e_group');
         $this->dropForeignKey('fk_g_curriculum_fkey','e_group');

        $this->addForeignKey(
            'fk_e_department_fkey',
            'e_group',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_g_education_type_fkey',
            'e_group',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_g_education_form_fkey',
            'e_group',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_g_specialty_fkey',
            'e_group',
            '_specialty',
            'e_specialty',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_g_curriculum_fkey',
            'e_group',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //h_semestr
         $this->dropForeignKey('fk_e_curriculum_h_semestr_fkey','h_semestr');
        $this->dropForeignKey('fk_h_education_year_h_semestr_fkey','h_semestr');

        $this->addForeignKey(
            'fk_e_curriculum_h_semestr_fkey',
            'h_semestr',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_h_semestr_fkey',
            'h_semestr',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );

        //e_student
        $this->dropForeignKey('fk_h_citizenship_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_country_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_province_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_district_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_accommodation_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_social_category_student_fkey', 'e_student');

        $this->alterColumn('e_student', '_citizenship', $this->string(64)->null());
        $this->alterColumn('e_student', '_country', $this->string(64)->null());
        $this->alterColumn('e_student', '_province', $this->string(64)->null());
        $this->alterColumn('e_student', '_district', $this->string(64)->null());
        $this->alterColumn('e_student', '_accommodation', $this->string(64)->null());
        $this->alterColumn('e_student', '_social_category', $this->string(64)->null());

        $this->addForeignKey(
            'fk_h_citizenship_student_fkey',
            'e_student',
            '_citizenship',
            'h_citizenship_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_country_student_fkey',
            'e_student',
            '_country',
            'h_country',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_province_student_fkey',
            'e_student',
            '_province',
            'h_soato',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_district_student_fkey',
            'e_student',
            '_district',
            'h_soato',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_accommodation_student_fkey',
            'e_student',
            '_accommodation',
            'h_accommodation',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_social_category_student_fkey',
            'e_student',
            '_social_category',
            'h_social_category',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_student_meta
        $this->dropForeignKey('fk_e_department_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_education_type_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_education_form_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_e_specialty_student_meta_fkey', 'e_student_meta');
         $this->dropForeignKey('fk_e_curriculum_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_course_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_e_group_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_education_year_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_payment_form_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_student_status_student_meta_fkey', 'e_student_meta');
        $this->addForeignKey(
            'fk_e_department_student_meta_fkey',
            'e_student_meta',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_type_student_meta_fkey',
            'e_student_meta',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_form_student_meta_fkey',
            'e_student_meta',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_specialty_student_meta_fkey',
            'e_student_meta',
            '_specialty',
            'e_specialty',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_curriculum_student_meta_fkey',
            'e_student_meta',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_course_student_meta_fkey',
            'e_student_meta',
            '_level',
            'h_course',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_student_meta_fkey',
            'e_student_meta',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_student_meta_fkey',
            'e_student_meta',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_payment_form_student_meta_fkey',
            'e_student_meta',
            '_payment_form',
            'h_payment_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_student_status_student_meta_fkey',
            'e_student_meta',
            '_student_status',
            'h_student_status',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_employee
        $this->dropForeignKey('fk_h_gender_employee_fkey', 'e_employee');
        $this->dropForeignKey('fk_h_academic_degree_employee_fkey', 'e_employee');
        $this->dropForeignKey('fk_h_academic_rank_employee_fkey', 'e_employee');

        $this->alterColumn('e_employee', '_gender', $this->string(64)->null());
        $this->alterColumn('e_employee', '_academic_degree', $this->string(64)->null());
        $this->alterColumn('e_employee', '_academic_rank', $this->string(64)->null());
        $this->addForeignKey(
            'fk_h_gender_employee_fkey',
            'e_employee',
            '_gender',
            'h_gender',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_academic_degree_employee_fkey',
            'e_employee',
            '_academic_degree',
            'h_academic_degree',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_academic_rank_employee_fkey',
            'e_employee',
            '_academic_rank',
            'h_academic_rank',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_employee_meta
        $this->dropForeignKey('fk_e_department_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_position_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_employment_form_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_employment_staff_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_employee_status_employee_meta_fkey', 'e_employee_meta');
        $this->addForeignKey(
            'fk_e_department_employee_meta_fkey',
            'e_employee_meta',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_position_employee_meta_fkey',
            'e_employee_meta',
            '_position',
            'h_teacher_position_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_employment_form_employee_meta_fkey',
            'e_employee_meta',
            '_employment_form',
            'h_employment_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_employment_staff_employee_meta_fkey',
            'e_employee_meta',
            '_employment_staff',
            'h_employment_staff',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_employee_status_employee_meta_fkey',
            'e_employee_meta',
            '_employee_status',
            'h_teacher_status',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_subject
       // $this->dropForeignKey('fk_h_subject_group_e_subject_fkey', 'e_subject');
        $this->dropForeignKey('fk_h_education_type_e_subject_fkey', 'e_subject');
        /*$this->addForeignKey(
            'fk_h_subject_group_e_subject_fkey',
            'e_subject',
            '_subject_group',
            'h_subject_group',
            'code',
            'RESTRICT',
            'CASCADE'
        );*/
        $this->addForeignKey(
            'fk_h_education_type_e_subject_fkey',
            'e_subject',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_curriculum_subject_block
         $this->dropForeignKey('fk_e_curriculum_curriculum_subject_block', 'e_curriculum_subject_block');
        $this->dropForeignKey('fk_h_curriculum_unit_curriculum_subject_block', 'e_curriculum_subject_block');
        $this->alterColumn('e_curriculum_subject_block', '_subject_block', $this->string(64)->null());
        $this->addForeignKey(
            'fk_e_curriculum_curriculum_subject_block',
            'e_curriculum_subject_block',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_curriculum_unit_curriculum_subject_block',
            'e_curriculum_subject_block',
            '_subject_block',
            'h_subject_block',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_curriculum_subject
        $this->dropForeignKey('fk_e_curriculum_curriculum_subject', 'e_curriculum_subject');
         $this->dropForeignKey('fk_e_subject_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_curriculum_subject_block_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_h_subject_type_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_h_rating_grade_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_h_exam_finish_curriculum_subject', 'e_curriculum_subject');
        $this->alterColumn('e_curriculum_subject', '_exam_finish', $this->string(64)->null());
        $this->addForeignKey(
            'fk_e_curriculum_curriculum_subject',
            'e_curriculum_subject',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_curriculum_subject',
            'e_curriculum_subject',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_curriculum_subject_block_curriculum_subject',
            'e_curriculum_subject',
            '_curriculum_subject_block',
            'h_subject_block',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_subject_type_curriculum_subject',
            'e_curriculum_subject',
            '_subject_type',
            'h_subject_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_rating_grade_curriculum_subject',
            'e_curriculum_subject',
            '_rating_grade',
            'h_rating_grade',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_exam_finish_curriculum_subject',
            'e_curriculum_subject',
            '_exam_finish',
            'h_exam_finish',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_curriculum_subject_detail
         $this->dropForeignKey('fk_e_curriculum_e_curriculum_subject_detail', 'e_curriculum_subject_detail');
        $this->dropForeignKey('fk_e_subject_e_curriculum_subject_detail', 'e_curriculum_subject_detail');
        $this->dropForeignKey('fk_h_training_type_e_curriculum_subject_detail', 'e_curriculum_subject_detail');

        $this->addForeignKey(
            'fk_e_curriculum_e_curriculum_subject_detail',
            'e_curriculum_subject_detail',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_curriculum_subject_detail',
            'e_curriculum_subject_detail',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_training_type_e_curriculum_subject_detail',
            'e_curriculum_subject_detail',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_curriculum_week
         $this->dropForeignKey('fk_e_curriculum_curriculum_week_fkey', 'e_curriculum_week');
        $this->dropForeignKey('fk_h_course_curriculum_week_fkey', 'e_curriculum_week');
        $this->dropForeignKey('fk_h_education_week_type_curriculum_week_fkey', 'e_curriculum_week');
        $this->addForeignKey(
            'fk_e_curriculum_curriculum_week_fkey',
            'e_curriculum_week',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_course_curriculum_week_fkey',
            'e_curriculum_week',
            '_level',
            'h_course',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_week_type_curriculum_week_fkey',
            'e_curriculum_week',
            '_education_week_type',
            'h_education_week_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_curriculum_subject_topic
         $this->dropForeignKey('fk_e_curriculum_curriculum_subject_topic_fkey', 'e_curriculum_subject_topic');
        $this->dropForeignKey('fk_e_subject_curriculum_subject_topic_fkey', 'e_curriculum_subject_topic');
        $this->addForeignKey(
            'fk_e_curriculum_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //e_auditorium
        $this->dropForeignKey('fk_h_building_e_auditorium_fkey', 'e_auditorium');
        $this->dropForeignKey('fk_h_auditorium_type_e_auditorium_fkey', 'e_auditorium');
        $this->alterColumn('e_auditorium', '_building', $this->integer()->null());
        $this->alterColumn('e_auditorium', '_auditorium_type', $this->string(64)->null());
        $this->addForeignKey(
            'fk_h_building_e_auditorium_fkey',
            'e_auditorium',
            '_building',
            'h_building',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_auditorium_type_e_auditorium_fkey',
            'e_auditorium',
            '_auditorium_type',
            'h_auditorium_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //h_lesson_pair
        $this->dropForeignKey('fk_h_education_year_h_lesson_pair_fkey', 'h_lesson_pair');
        $this->alterColumn('h_lesson_pair', '_education_year', $this->string(64)->notNull());
        $this->addForeignKey(
            'fk_h_education_year_h_lesson_pair_fkey',
            'h_lesson_pair',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );

        //e_subject_schedule
        $this->dropForeignKey('fk_e_curriculum_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_subject_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_h_education_year_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_group_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_training_type_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_auditorium_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_curriculum_subject_topic_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_curriculum_week_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_employee_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->alterColumn('e_subject_schedule', '_education_year', $this->string(64)->notNull());
        $this->alterColumn('e_subject_schedule', '_training_type', $this->string(64)->null());
        $this->alterColumn('e_subject_schedule', '_auditorium', $this->integer()->null());
        $this->alterColumn('e_subject_schedule', '_week', $this->integer()->null());
        $this->addForeignKey(
            'fk_e_curriculum_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_training_type_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_training_type',
            'h_training_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_auditorium_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_auditorium',
            'e_auditorium',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_curriculum_subject_topic_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_subject_topic',
            'e_curriculum_subject_topic',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_curriculum_week_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_week',
            'e_curriculum_week',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //e_subject_exam_schedule
        $this->dropForeignKey('e_subject_exam_schedule_curriculum_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_subject_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_education_year_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_group_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_exam_type_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('fk_e_auditorium_subject_exam_schedule_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('fk_e_curriculum_week_subject_exam_schedule_fkey', 'e_subject_exam_schedule');///
        $this->dropForeignKey('fk_e_employee_subject_exam_schedule_fkey', 'e_subject_exam_schedule');///
        $this->alterColumn('e_subject_exam_schedule', '_education_year', $this->string(64)->notNull());
        $this->alterColumn('e_subject_exam_schedule', '_exam_type', $this->string(64)->null());
        $this->alterColumn('e_subject_exam_schedule', '_auditorium', $this->integer()->null());
        $this->alterColumn('e_subject_exam_schedule', '_week', $this->integer()->null());
        $this->addForeignKey(
            'e_subject_exam_schedule_curriculum_fkey',
            'e_subject_exam_schedule',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'e_subject_exam_schedule_subject_fkey',
            'e_subject_exam_schedule',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'e_subject_exam_schedule_education_year_fkey',
            'e_subject_exam_schedule',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'e_subject_exam_schedule_group_fkey',
            'e_subject_exam_schedule',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'e_subject_exam_schedule_exam_type_fkey',
            'e_subject_exam_schedule',
            '_exam_type',
            'h_exam_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_auditorium_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_auditorium',
            'e_auditorium',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_curriculum_week_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_week',
            'e_curriculum_week',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //e_attendance
        $this->dropForeignKey('fk_e_subject_schedule_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_e_student_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_h_education_year_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_e_subject_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_training_type_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_e_employee_e_attendance_fkey', 'e_attendance');
        $this->alterColumn('e_attendance', '_education_year', $this->string(64)->notNull());
        $this->addForeignKey(
            'fk_e_subject_schedule_e_attendance_fkey',
            'e_attendance',
            '_subject_schedule',
            'e_subject_schedule',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_e_attendance_fkey',
            'e_attendance',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_attendance_fkey',
            'e_attendance',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_attendance_fkey',
            'e_attendance',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_training_type_e_attendance_fkey',
            'e_attendance',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_e_attendance_fkey',
            'e_attendance',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //e_performance
        $this->dropForeignKey('e_exam_schedule_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_student_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_h_education_year_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_subject_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_exam_type_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_employee_subject_e_performance_fkey', 'e_performance');
        //$this->dropForeignKey('fk_h_final_exam_type_e_performance_fkey', 'e_performance');
        $this->alterColumn('e_performance', '_education_year', $this->string(64)->notNull());

        $this->addForeignKey(
            'e_exam_schedule_e_performance_fkey',
            'e_performance',
            '_exam_schedule',
            'e_subject_exam_schedule',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_e_performance_fkey',
            'e_performance',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_performance_fkey',
            'e_performance',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_performance_fkey',
            'e_performance',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_exam_type_e_performance_fkey',
            'e_performance',
            '_exam_type',
            'h_exam_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_subject_e_performance_fkey',
            'e_performance',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        /*$this->addForeignKey(
            'fk_h_final_exam_type_e_performance_fkey',
            'e_performance',
            '_final_exam_type',
            'h_final_exam_type',
            'code',
            'NO ACTION',
            'CASCADE'
        );*/

        //e_attendance_control
        $this->dropForeignKey('fk_e_subject_schedule_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_e_group_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_h_education_year_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_e_subject_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_training_type_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_e_employee_e_attendance_control_fkey', 'e_attendance_control');
        $this->alterColumn('e_attendance_control', '_education_year', $this->string(64)->notNull());
        $this->addForeignKey(
            'fk_e_subject_schedule_e_attendance_control_fkey',
            'e_attendance_control',
            '_subject_schedule',
            'e_subject_schedule',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_attendance_control_fkey',
            'e_attendance_control',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_attendance_control_fkey',
            'e_attendance_control',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_attendance_control_fkey',
            'e_attendance_control',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_training_type_e_attendance_control_fkey',
            'e_attendance_control',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_e_attendance_control_fkey',
            'e_attendance_control',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //e_performance_control
        $this->dropForeignKey('e_exam_schedule_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_group_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_h_education_year_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_subject_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_employee_subject_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_exam_type_e_performance_control_fkey', 'e_performance_control');
        $this->alterColumn('e_performance_control', '_education_year', $this->string(64)->notNull());
        $this->addForeignKey(
            'e_exam_schedule_e_performance_control_fkey',
            'e_performance_control',
            '_exam_schedule',
            'e_subject_exam_schedule',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_performance_control_fkey',
            'e_performance_control',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_performance_control_fkey',
            'e_performance_control',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_performance_control_fkey',
            'e_performance_control',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_subject_e_performance_control_fkey',
            'e_performance_control',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_exam_type_e_performance_control_fkey',
            'e_performance_control',
            '_exam_type',
            'h_exam_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_curriculum_subject_exam_type
        $this->dropForeignKey('fk_e_curriculum_e_curriculum_subject_exam_type', 'e_curriculum_subject_exam_type');
        $this->dropForeignKey('fk_e_subject_e_curriculum_subject_exam_type', 'e_curriculum_subject_exam_type');
        $this->dropForeignKey('fk_h_exam_type_e_curriculum_subject_exam_type', 'e_curriculum_subject_exam_type');
        $this->alterColumn('e_curriculum_subject_exam_type', '_exam_type', $this->string(64)->null());
        $this->addForeignKey(
            'fk_e_curriculum_e_curriculum_subject_exam_type',
            'e_curriculum_subject_exam_type',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_curriculum_subject_exam_type',
            'e_curriculum_subject_exam_type',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_exam_type_e_curriculum_subject_exam_type',
            'e_curriculum_subject_exam_type',
            '_exam_type',
            'h_exam_type',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_academic_record
        $this->dropForeignKey('fk_e_curriculum_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_h_education_year_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_e_student_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_e_subject_e_academic_record_fkey', 'e_academic_record');
        $this->alterColumn('e_academic_record', '_education_year', $this->string(64)->notNull());

        $this->addForeignKey(
            'fk_e_curriculum_e_academic_record_fkey',
            'e_academic_record',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_academic_record_fkey',
            'e_academic_record',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_e_academic_record_fkey',
            'e_academic_record',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_academic_record_fkey',
            'e_academic_record',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //e_student_diploma
        $this->dropForeignKey('fk_e_student_e_student_diploma_fkey', 'e_student_diploma');
        $this->dropForeignKey('fk_e_specialty_e_student_diploma_fkey', 'e_student_diploma');
        $this->addForeignKey(
            'fk_e_student_e_student_diploma_fkey',
            'e_student_diploma',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
       $this->addForeignKey(
            'fk_e_specialty_e_student_diploma_fkey',
            'e_student_diploma',
            '_specialty',
            'e_specialty',
            'code',
            'NO ACTION',
            'CASCADE'
        );

       //e_student_employment
        $this->dropForeignKey('fk_e_student_e_student_employment_fkey', 'e_student_employment');
        $this->addForeignKey(
            'fk_e_student_e_student_employment_fkey',
            'e_student_employment',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        //h_grade_type
        $this->dropForeignKey('fk_h_marking_system_h_grade_type_fkey', 'h_grade_type');
        $this->addForeignKey(
            'fk_h_marking_system_h_grade_type_fkey',
            'h_grade_type',
            '_marking_system',
            'h_marking_system',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        //e_attendance_setting_border
        $this->dropForeignKey('fk_h_attendance_setting_e_attendance_setting_border_fkey', 'e_attendance_setting_border');
        $this->dropForeignKey('fk_h_marking_system_e_attendance_setting_border_fkey', 'e_attendance_setting_border');
        $this->alterColumn('e_attendance_setting_border', '_attendance_setting', $this->string(64)->null());
        $this->alterColumn('e_attendance_setting_border', '_marking_system', $this->string(64)->null());

        $this->addForeignKey(
            'fk_h_attendance_setting_e_attendance_setting_border_fkey',
            'e_attendance_setting_border',
            '_attendance_setting',
            'h_attendance_setting',
            'code',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_marking_system_e_attendance_setting_border_fkey',
            'e_attendance_setting_border',
            '_marking_system',
            'h_marking_system',
            'code',
            'SET NULL',
            'CASCADE'
        );

        //e_student_subject
        $this->dropForeignKey('fk_e_curriculum_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_e_subject_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_e_student_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_e_group_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_h_education_year_e_student_subject_fkey', 'e_student_subject');

        $this->addForeignKey(
            'fk_e_curriculum_e_student_subject_fkey',
            'e_student_subject',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_subject_e_student_subject_fkey',
            'e_student_subject',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_e_student_subject_fkey',
            'e_student_subject',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_student_subject_fkey',
            'e_student_subject',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_student_subject_fkey',
            'e_student_subject',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_h_ownership','e_university');
        $this->dropForeignKey('fk_h_university_form','e_university');
        $this->dropForeignKey('fk_e_university_id','e_department');
        $this->dropForeignKey('e_department_structure_type_fkey','e_department');
        $this->dropForeignKey('fk_e_department_id_fkey','e_specialty');
        $this->dropForeignKey('fk_s_education_type_fkey','e_specialty');
        $this->dropForeignKey('fk_c_department_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_education_type_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_education_form_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_specialty_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_marking_system_fkey','e_curriculum');
        $this->dropForeignKey('fk_c_education_year_fkey','e_curriculum');
        $this->dropForeignKey('fk_e_department_fkey','e_group');
        $this->dropForeignKey('fk_g_education_type_fkey','e_group');
        $this->dropForeignKey('fk_g_education_form_fkey','e_group');
        $this->dropForeignKey('fk_g_specialty_fkey','e_group');
        $this->dropForeignKey('fk_g_curriculum_fkey','e_group');//
        $this->dropForeignKey('fk_e_curriculum_h_semestr_fkey','h_semestr');//
        $this->dropForeignKey('fk_h_education_year_h_semestr_fkey','h_semestr');
        $this->dropForeignKey('fk_h_citizenship_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_country_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_province_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_district_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_accommodation_student_fkey', 'e_student');
        $this->dropForeignKey('fk_h_social_category_student_fkey', 'e_student');
        $this->dropForeignKey('fk_e_department_student_meta_fkey', 'e_student_meta');//
        $this->dropForeignKey('fk_h_education_type_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_education_form_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_e_specialty_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_e_curriculum_student_meta_fkey', 'e_student_meta');//
        $this->dropForeignKey('fk_h_course_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_e_group_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_education_year_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_payment_form_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_student_status_student_meta_fkey', 'e_student_meta');
        $this->dropForeignKey('fk_h_gender_employee_fkey', 'e_employee');
        $this->dropForeignKey('fk_h_academic_degree_employee_fkey', 'e_employee');
        $this->dropForeignKey('fk_h_academic_rank_employee_fkey', 'e_employee');
        $this->dropForeignKey('fk_e_department_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_position_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_employment_form_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_employment_staff_employee_meta_fkey', 'e_employee_meta');
        $this->dropForeignKey('fk_h_employee_status_employee_meta_fkey', 'e_employee_meta');
        //$this->dropForeignKey('fk_h_subject_group_e_subject_fkey', 'e_subject');
        $this->dropForeignKey('fk_h_education_type_e_subject_fkey', 'e_subject');
        $this->dropForeignKey('fk_e_curriculum_curriculum_subject_block', 'e_curriculum_subject_block');//
        $this->dropForeignKey('fk_h_curriculum_unit_curriculum_subject_block', 'e_curriculum_subject_block');
        $this->dropForeignKey('fk_e_curriculum_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_e_subject_curriculum_subject', 'e_curriculum_subject');//
        $this->dropForeignKey('fk_curriculum_subject_block_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_h_subject_type_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_h_rating_grade_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_h_exam_finish_curriculum_subject', 'e_curriculum_subject');
        $this->dropForeignKey('fk_e_curriculum_e_curriculum_subject_detail', 'e_curriculum_subject_detail');//
        $this->dropForeignKey('fk_e_subject_e_curriculum_subject_detail', 'e_curriculum_subject_detail');
        $this->dropForeignKey('fk_h_training_type_e_curriculum_subject_detail', 'e_curriculum_subject_detail');
        $this->dropForeignKey('fk_e_curriculum_curriculum_week_fkey', 'e_curriculum_week');//
        $this->dropForeignKey('fk_h_course_curriculum_week_fkey', 'e_curriculum_week');
        $this->dropForeignKey('fk_h_education_week_type_curriculum_week_fkey', 'e_curriculum_week');
        $this->dropForeignKey('fk_e_curriculum_curriculum_subject_topic_fkey', 'e_curriculum_subject_topic');//
        $this->dropForeignKey('fk_e_subject_curriculum_subject_topic_fkey', 'e_curriculum_subject_topic');
        $this->dropForeignKey('fk_h_building_e_auditorium_fkey', 'e_auditorium');
        $this->dropForeignKey('fk_h_auditorium_type_e_auditorium_fkey', 'e_auditorium');
        $this->dropForeignKey('fk_h_education_year_h_lesson_pair_fkey', 'h_lesson_pair');
        $this->dropForeignKey('fk_e_curriculum_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_subject_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_h_education_year_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_group_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_training_type_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_auditorium_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_curriculum_subject_topic_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_curriculum_week_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('fk_e_employee_e_subject_schedule_fkey', 'e_subject_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_curriculum_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_subject_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_education_year_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_group_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('e_subject_exam_schedule_exam_type_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('fk_e_auditorium_subject_exam_schedule_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('fk_e_curriculum_week_subject_exam_schedule_fkey', 'e_subject_exam_schedule');//
        $this->dropForeignKey('fk_e_employee_subject_exam_schedule_fkey', 'e_subject_exam_schedule');//
        $this->dropForeignKey('fk_e_subject_schedule_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_e_student_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_h_education_year_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_e_subject_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_training_type_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('fk_e_employee_e_attendance_fkey', 'e_attendance');
        $this->dropForeignKey('e_exam_schedule_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_student_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_h_education_year_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_subject_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_exam_type_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_employee_subject_e_performance_fkey', 'e_performance');
        $this->dropForeignKey('fk_e_subject_schedule_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_e_group_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_h_education_year_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_e_subject_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_training_type_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('fk_e_employee_e_attendance_control_fkey', 'e_attendance_control');
        $this->dropForeignKey('e_exam_schedule_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_group_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_h_education_year_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_subject_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_employee_subject_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_exam_type_e_performance_control_fkey', 'e_performance_control');
        $this->dropForeignKey('fk_e_curriculum_e_curriculum_subject_exam_type', 'e_curriculum_subject_exam_type');
        $this->dropForeignKey('fk_e_subject_e_curriculum_subject_exam_type', 'e_curriculum_subject_exam_type');
        $this->dropForeignKey('fk_h_exam_type_e_curriculum_subject_exam_type', 'e_curriculum_subject_exam_type');
        $this->dropForeignKey('fk_e_curriculum_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_h_education_year_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_e_student_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_e_subject_e_academic_record_fkey', 'e_academic_record');
        $this->dropForeignKey('fk_e_student_e_student_diploma_fkey', 'e_student_diploma');
        $this->dropForeignKey('fk_e_specialty_e_student_diploma_fkey', 'e_student_diploma');
        $this->dropForeignKey('fk_e_student_e_student_employment_fkey', 'e_student_employment');
        $this->dropForeignKey('fk_h_marking_system_h_grade_type_fkey', 'h_grade_type');
        $this->dropForeignKey('fk_h_attendance_setting_e_attendance_setting_border_fkey', 'e_attendance_setting_border');
        $this->dropForeignKey('fk_h_marking_system_e_attendance_setting_border_fkey', 'e_attendance_setting_border');
        $this->dropForeignKey('fk_e_curriculum_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_e_subject_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_e_student_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_e_group_e_student_subject_fkey', 'e_student_subject');
        $this->dropForeignKey('fk_h_education_year_e_student_subject_fkey', 'e_student_subject');
    }

}
