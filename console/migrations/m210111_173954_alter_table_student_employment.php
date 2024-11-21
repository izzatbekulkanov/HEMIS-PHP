<?php

use yii\db\Migration;

/**
 * Class m210111_173954_alter_table_student_employment
 */
class m210111_173954_alter_table_student_employment extends Migration
{
    public function safeUp()
    {
       $this->addColumn('e_student_employment', '_employment_status', $this->string(64)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_employment', '_employment_status');
    }
}
