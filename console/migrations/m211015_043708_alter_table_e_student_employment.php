<?php

use yii\db\Migration;

/**
 * Class m211015_043708_alter_table_e_student_employment
 */
class m211015_043708_alter_table_e_student_employment extends Migration
{

    public function safeUp()
    {
        $this->dropColumn('e_student_employment', '_graduate_inactive_type');
        $this->addColumn('e_student_employment', '_graduate_inactive', $this->string(64)->null());
    }


    public function safeDown()
    {
        $this->addColumn('e_student_employment', '_graduate_inactive_type', $this->string(64)->null());
        $this->dropColumn('e_student_employment', '_graduate_inactive');
    }

}
