<?php

use yii\db\Migration;

/**
 * Class m211015_200609_add_column_group_e_student_employment
 */
class m211015_200609_add_column_group_e_student_employment extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_employment', '_group', $this->integer()->null());
        $this->addForeignKey(
            'fk_e_group_e_student_employment_fkey',
            'e_student_employment',
            '_group',
            'e_group',
            'id',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_employment', '_group');
    }
}
