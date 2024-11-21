<?php

use yii\db\Migration;

/**
 * Class m201110_103224_alter_table_student
 */
class m201110_103224_alter_table_student extends Migration
{

    public function safeUp()
    {
        $this->addColumn('e_student', 'pin_verified', $this->integer(3)->defaultValue(0));
    }


    public function safeDown()
    {
        $this->dropColumn('e_student', 'pin_verified');
    }

}
