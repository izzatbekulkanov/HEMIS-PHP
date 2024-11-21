<?php

use yii\db\Migration;

/**
 * Class m210828_055946_alter_add_level_student_contract_table
 */
class m210828_055946_alter_add_level_student_contract_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', '_level', $this->string(64)->null());
        $this->addForeignKey(
            'fk_e_student_contract_level',
            'e_student_contract',
            '_level',
            'h_course',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        if ($count = $this->getDb()->createCommand("
UPDATE e_student_contract
SET _level=h_semestr._level
FROM h_semestr
WHERE e_student_contract._education_year = h_semestr._education_year and e_student_contract._curriculum = h_semestr._curriculum and h_semestr._level is not null
")->execute()) {
            echo "$count contracts updated by level\n";
        }
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', '_level');
    }


}
