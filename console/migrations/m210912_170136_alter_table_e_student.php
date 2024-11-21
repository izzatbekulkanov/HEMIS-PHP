<?php

use yii\db\Migration;

/**
 * Class m210912_170136_alter_table_e_student
 */
class m210912_170136_alter_table_e_student extends Migration
{

    public function safeUp()
    {
        $this->addColumn('e_student', '_current_province', $this->string(64));
        $this->addColumn('e_student', '_current_district', $this->string(64));

        $this->addForeignKey(
            'fk_e_student_current_province',
            'e_student',
            '_current_province',
            'h_soato',
            'code',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_current_district',
            'e_student',
            '_current_district',
            'h_soato',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_student', '_current_province');
        $this->dropColumn('e_student', '_current_district');
    }
}
