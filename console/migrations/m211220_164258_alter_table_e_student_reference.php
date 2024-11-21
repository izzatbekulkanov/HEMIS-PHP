<?php

use yii\db\Migration;

/**
 * Class m211220_164258_alter_table_e_student_reference
 */
class m211220_164258_alter_table_e_student_reference extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_reference', '_group', $this->integer()->null());
        $this->addForeignKey(
            'fk_e_group_e_student_reference_fkey',
            'e_student_reference',
            '_group',
            'e_group',
            'id',
            'RESTRICT'
        );

        $this->addColumn('e_student_reference', 'department_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'specialty_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'group_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'education_type_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'education_form_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'education_year_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'curriculum_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'semester_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'level_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'citizenship_name', $this->string(255)->null());
        $this->addColumn('e_student_reference', 'payment_form_name', $this->string(255)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_reference', '_group');
        $this->dropColumn('e_student_reference', 'department_name');
        $this->dropColumn('e_student_reference', 'specialty_name');
        $this->dropColumn('e_student_reference', 'group_name');
        $this->dropColumn('e_student_reference', 'education_type_name');
        $this->dropColumn('e_student_reference', 'education_form_name');
        $this->dropColumn('e_student_reference', 'education_year_name');
        $this->dropColumn('e_student_reference', 'curriculum_name');
        $this->dropColumn('e_student_reference', 'semester_name');
        $this->dropColumn('e_student_reference', 'level_name');
        $this->dropColumn('e_student_reference', 'citizenship_name');
        $this->dropColumn('e_student_reference', 'payment_form_name');
    }
}
