<?php

use yii\db\Migration;

/**
 * Class m210821_082619_alter_e_student_contract
 */
class m210821_082619_alter_e_student_contract extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', 'month_count', $this->integer()->defaultValue(12));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', 'month_count');
    }
}
