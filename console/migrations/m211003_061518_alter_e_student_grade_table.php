<?php

use yii\db\Migration;

/**
 * Class m211003_061518_alter_e_student_grade_table
 */
class m211003_061518_alter_e_student_grade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->dropForeignKey(
            'fk_e_subject_schedule_e_student_grade_fkey',
            'e_student_grade'
        );

        $this->dropForeignKey(
            'fk_e_student_e_student_grade_fkey',
            'e_student_grade'
        );

        $this->dropForeignKey(
            'fk_h_education_year_e_student_grade_fkey',
            'e_student_grade'
        );

        $this->dropForeignKey(
            'fk_e_subject_e_student_grade_fkey',
            'e_student_grade'
        );

        $this->dropForeignKey(
            'fk_training_type_e_student_grade_fkey',
            'e_student_grade'
        );

        $this->dropForeignKey(
            'fk_e_employee_e_student_grade_fkey',
            'e_student_grade'
        );

        $this->dropForeignKey(
            '{{%fk-e_student_grade-_subject_topic}}',
            '{{%e_student_grade}}'
        );

        $this->dropIndex(
            'e_student_grade_student_uniq',
            'e_student_grade'
        );

        // Re-add

        $this->addForeignKey(
            'fk_e_subject_schedule_e_student_grade_fkey',
            'e_student_grade',
            '_subject_schedule',
            'e_subject_schedule',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_e_student_e_student_grade_fkey',
            'e_student_grade',
            '_student',
            'e_student',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_h_education_year_e_student_grade_fkey',
            'e_student_grade',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_e_subject_e_student_grade_fkey',
            'e_student_grade',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_training_type_e_student_grade_fkey',
            'e_student_grade',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_e_employee_e_student_grade_fkey',
            'e_student_grade',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            '{{%fk-e_student_grade-_subject_topic}}',
            '{{%e_student_grade}}',
            '_subject_topic',
            '{{%e_curriculum_subject_topic}}',
            'id',
            'RESTRICT'
        );

        $this->createIndex(
            'e_student_grade_student_uniq',
            'e_student_grade',
            ['_student', '_employee', '_subject_schedule', '_subject_topic'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Nothing
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211003_061518_alter_e_student_grade_table cannot be reverted.\n";

        return false;
    }
    */
}
