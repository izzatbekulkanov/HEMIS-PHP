<?php

use yii\db\Migration;
use common\models\archive\EStudentEmployment;

/**
 * Class m211015_173123_alter_table_e_student_employment_null_values
 */
class m211015_173123_alter_table_e_student_employment_null_values extends Migration
{
    public function safeUp()
    {
        $this->alterColumn(EStudentEmployment::tableName(), 'employment_doc_number', $this->string(20)->null());
        $this->alterColumn(EStudentEmployment::tableName(), 'employment_doc_date', $this->date()->null());
        $this->alterColumn(EStudentEmployment::tableName(), 'company_name', $this->string(256)->null());
        $this->alterColumn(EStudentEmployment::tableName(), 'position_name', $this->string(256)->null());
        $this->alterColumn(EStudentEmployment::tableName(), 'start_date', $this->date()->null());

        $this->addColumn('e_student_employment', '_payment_form', $this->string(64)->null());
        $this->addForeignKey(
            'fk_h_payment_form_e_student_employment_fkey',
            'e_student_employment',
            '_payment_form',
            'h_payment_form',
            'code',
            'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->alterColumn(EStudentEmployment::tableName(), 'employment_doc_number', $this->string(20)->notNull());
        $this->alterColumn(EStudentEmployment::tableName(), 'employment_doc_date', $this->date()->notNull());
        $this->alterColumn(EStudentEmployment::tableName(), 'company_name', $this->string(256)->notNull());
        $this->alterColumn(EStudentEmployment::tableName(), 'position_name', $this->string(256)->notNull());
        $this->alterColumn(EStudentEmployment::tableName(), 'start_date', $this->date()->notNull());
        $this->dropColumn('e_student_employment', '_payment_form');
    }
}
