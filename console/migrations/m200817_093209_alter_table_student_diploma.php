<?php

use common\models\archive\EAcademicRecord;
use common\models\archive\EStudentDiploma;
use yii\db\Migration;

/**
 * Class m200817_093209_alter_table_student_diploma
 */
class m200817_093209_alter_table_student_diploma extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn(EAcademicRecord::tableName(), 'student', 'student_name');
        $this->renameColumn(EAcademicRecord::tableName(), 'curriculum', 'curriculum_name');
        $this->renameColumn(EAcademicRecord::tableName(), 'education_year', 'education_year_name');
        $this->renameColumn(EAcademicRecord::tableName(), 'semester', 'semester_name');
        $this->renameColumn(EAcademicRecord::tableName(), 'subject', 'subject_name');
        $this->renameColumn(EAcademicRecord::tableName(), 'employee', 'employee_name');

        $this->renameColumn(EStudentDiploma::tableName(), 'specialty', 'specialty_name');
        $this->renameColumn(EStudentDiploma::tableName(), 'student', 'student_name');
        $this->renameColumn(EStudentDiploma::tableName(), 'department', 'department_name');

        $this->addColumn(EStudentDiploma::tableName(), '_uid', $this->string()->unique());
        $this->addColumn(EStudentDiploma::tableName(), '_sync', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn(EAcademicRecord::tableName(), 'student_name', 'student');
        $this->renameColumn(EAcademicRecord::tableName(), 'curriculum_name', 'curriculum');
        $this->renameColumn(EAcademicRecord::tableName(), 'education_year_name', 'education_year');
        $this->renameColumn(EAcademicRecord::tableName(), 'semester_name', 'semester');
        $this->renameColumn(EAcademicRecord::tableName(), 'subject_name', 'subject');
        $this->renameColumn(EAcademicRecord::tableName(), 'employee_name', 'employee');

        $this->renameColumn(EStudentDiploma::tableName(), 'specialty_name', 'specialty');
        $this->renameColumn(EStudentDiploma::tableName(), 'student_name', 'student');
        $this->renameColumn(EStudentDiploma::tableName(), 'department_name', 'department');


        $this->dropColumn(EStudentDiploma::tableName(), '_uid');
        $this->dropColumn(EStudentDiploma::tableName(), '_sync');
    }

}
