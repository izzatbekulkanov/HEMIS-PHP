<?php

use yii\db\Migration;

/**
 * Class m211109_135312_alter_table_e_attendance_activity
 */
class m211109_135312_alter_table_e_attendance_activity extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_attendance_activity', 'file', 'jsonb');
    }

    public function safeDown()
    {
        $this->dropColumn('e_attendance_activity', 'file');
    }
}
