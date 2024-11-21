<?php

use yii\db\Migration;
use common\models\system\SystemClassifier;

/**
 * Handles adding columns to table `{{%e_student}}`.
 */
class m211030_132314_add_column_to_e_student_table extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);
        $this->addColumn('e_student', '_student_type', $this->string(64)->defaultValue('11'));

        $this->addForeignKey(
            'fk_h_student_type_e_student_fkey',
            'e_student',
            '_student_type',
            'h_student_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_student', '_student_type');
    }
}
