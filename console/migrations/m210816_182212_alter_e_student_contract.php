<?php

use yii\db\Migration;

/**
 * Class m210816_182212_alter_e_student_contract
 */
class m210816_182212_alter_e_student_contract extends Migration
{

    public function safeUp()
    {
        $this->dropColumn('e_student_contract', 'discount');
        $this->addColumn('e_student_contract', 'discount', $this->decimal(5, 1)->defaultValue(0.0));
    }

    public function safeDown()
    {
        $this->addColumn('e_student_contract', 'discount', $this->decimal(5, 1));
        $this->alterColumn('e_student_contract', 'discount', $this->decimal(5, 1));
    }
}
