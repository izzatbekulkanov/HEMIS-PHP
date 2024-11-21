<?php

use yii\db\Migration;

/**
 * Class m210809_070719_alter_tables_for_academic_sheet
 */
class m210809_070719_alter_tables_for_academic_sheet extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_graduate_qualifying_work', '_decree', $this->integer());
        $this->addColumn('e_student', '_decree_enroll', $this->integer());

        $this->addForeignKey(
            'fk_e_graduate_qualifying_work_decree',
            'e_graduate_qualifying_work',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_decree_enroll',
            'e_student',
            '_decree_enroll',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->alterColumn('e_decree_student', '_student_meta', $this->integer()->null());
    }


    public function safeDown()
    {
        $this->dropColumn('e_graduate_qualifying_work', '_decree');
        $this->dropColumn('e_student', '_decree_enroll');
        $this->alterColumn('e_decree_student', '_student_meta', $this->integer()->notNull());
    }
}
