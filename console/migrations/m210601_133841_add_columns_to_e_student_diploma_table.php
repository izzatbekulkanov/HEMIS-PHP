<?php

use common\models\archive\EStudentDiploma;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_student_diploma}}`.
 */
class m210601_133841_add_columns_to_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn(EStudentDiploma::tableName(), 'admission_information', $this->string(1000));
        $this->addColumn(EStudentDiploma::tableName(), 'qualification_data', $this->string(1500));
        $this->addColumn(EStudentDiploma::tableName(), 'next_edu_information', $this->string(1000));
        $this->addColumn(EStudentDiploma::tableName(), 'given_hei_information', $this->string(1000));
        $this->addColumn(EStudentDiploma::tableName(), 'professional_activity', $this->string(1000));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(EStudentDiploma::tableName(), 'admission_information');
        $this->dropColumn(EStudentDiploma::tableName(), 'qualification_data');
        $this->dropColumn(EStudentDiploma::tableName(), 'next_edu_information');
        $this->dropColumn(EStudentDiploma::tableName(), 'given_hei_information');
        $this->dropColumn(EStudentDiploma::tableName(), 'professional_activity');
    }
}
