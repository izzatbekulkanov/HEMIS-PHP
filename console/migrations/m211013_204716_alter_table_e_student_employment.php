<?php

use yii\db\Migration;
use common\models\system\SystemClassifier;

/**
 * Class m211013_204716_alter_table_e_student_employment
 */
class m211013_204716_alter_table_e_student_employment extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);
        $this->addColumn('e_student_employment', '_graduate_fields_type', $this->string(64)->null());
        $this->addColumn('e_student_employment', '_graduate_inactive_type', $this->string(64)->null());
        $this->addColumn('e_student_employment', 'workplace_compatibility ', $this->string(64)->null());
        $this->addForeignKey(
            'fk_e_student_employment_graduate_fields_type',
            'e_student_employment',
            '_graduate_fields_type',
            'h_graduate_fields_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_employment_graduate_inactive_type',
            'e_student_employment',
            '_graduate_inactive_type',
            'h_graduate_inactive_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_employment', '_graduate_fields_type');
        $this->dropColumn('e_student_employment', '_graduate_inactive_type');
        $this->dropColumn('e_student_employment', 'workplace_compatibility');
    }
}
