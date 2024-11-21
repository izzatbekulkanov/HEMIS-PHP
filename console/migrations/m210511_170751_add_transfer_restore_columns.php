<?php

use yii\db\Migration;

/**
 * Class m210511_170751_add_transfer_restore_columns
 */
class m210511_170751_add_transfer_restore_columns extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_meta', '_restore_meta_id', $this->integer()->null());
        $this->addColumn('e_student_meta', 'subjects_map', $this->string()->null());

        $this->addForeignKey(
            'fk_e_student_meta_restore_meta_id',
            'e_student_meta',
            '_restore_meta_id',
            'e_student_meta',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }


    public function safeDown()
    {
        $this->dropColumn('e_student_meta', '_restore_meta_id');
        $this->dropColumn('e_student_meta', 'subjects_map');
    }

}
