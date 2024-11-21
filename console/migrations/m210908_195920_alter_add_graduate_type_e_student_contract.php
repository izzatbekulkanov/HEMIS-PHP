<?php

use yii\db\Migration;

/**
 * Class m210908_195920_alter_add_graduate_type_e_student_contract
 */
class m210908_195920_alter_add_graduate_type_e_student_contract extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', '_graduate_type', $this->string(64)->defaultValue('11'));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', '_graduate_type');
    }
}
