<?php

use common\models\student\EStudentAward;
use yii\db\Migration;

/**
 * Class m210329_162000_alter_table_e_student_award
 */
class m210329_162000_alter_table_e_student_award extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(EStudentAward::tableName(), '_education_type', $this->string(64));
        $this->addColumn(EStudentAward::tableName(), '_education_form', $this->string(64));

        $this->addForeignKey(
            'fk_h_education_type_e_student_award_fkey',
            'e_student_award',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_student_award_fkey',
            'e_student_award',
            '_education_form',
            'h_education_form',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_h_education_type_e_student_award_fkey', 'e_student_award');
        $this->dropForeignKey('fk_h_education_form_e_student_award_fkey', 'e_student_award');
        $this->dropColumn(EStudentAward::tableName(), '_education_type');
        $this->dropColumn(EStudentAward::tableName(), '_education_form');
    }
}
