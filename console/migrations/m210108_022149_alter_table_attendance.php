<?php

use yii\db\Migration;

/**
 * Class m210108_022149_alter_table_attendance
 */
class m210108_022149_alter_table_attendance extends Migration
{
    
    public function safeUp()
    {
		$this->addColumn('e_attendance', 'accept_for_change', $this->boolean()->defaultValue(false));
		$this->addColumn('e_attendance', 'deadline_for_change', $this->date()->null());
		$this->addColumn('e_attendance', 'reason_for_change', $this->string(255)->null());
		$this->addColumn('e_attendance', 'status_for_change', $this->integer(3)->null());
		
		$this->addColumn('e_attendance', 'accept_for_rework', $this->boolean()->defaultValue(false));
		$this->addColumn('e_attendance', 'deadline_for_rework', $this->date()->null());
		$this->addColumn('e_attendance', 'result_for_rework', $this->string(255)->null());
		$this->addColumn('e_attendance', 'status_for_rework', $this->integer(3)->null());
		$this->addColumn('e_attendance', 'reworked_date', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_attendance', 'accept_for_change');
        $this->dropColumn('e_attendance', 'deadline_for_change');
        $this->dropColumn('e_attendance', 'reason_for_change');
        $this->dropColumn('e_attendance', 'status_for_change');
        $this->dropColumn('e_attendance', 'accept_for_rework');
        $this->dropColumn('e_attendance', 'deadline_for_rework');
        $this->dropColumn('e_attendance', 'result_for_rework');
        $this->dropColumn('e_attendance', 'status_for_rework');
        $this->dropColumn('e_attendance', 'reworked_date');
    }

   
}
