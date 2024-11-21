<?php

use yii\db\Migration;

/**
 * Class m210909_143418_alter_table_student
 */
class m210909_143418_alter_table_student extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student', 'phone', $this->string(20));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student', 'phone');
    }

}
