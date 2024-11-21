<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_attendance_activity}}`.
 */
class m210109_173420_create_e_attendance_activity_table extends Migration
{
     public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Davomat faoliyati bo`yicha amallar';
		
        $this->createTable('e_attendance_activity', [
            'id' => $this->primaryKey(),
            '_attendance'=>$this->integer()->notNull(),
            '_employee'=>$this->integer(),
            'accepted'=>$this->boolean()->defaultValue(false),
            'deadline'=>$this->date(),
            'reason'=>$this->string(255)->null(),
            'status_for_activity'=>$this->integer(3)->null(),
			'reworked_date'=>$this->dateTime()->null(),
			'absent_on' => $this->smallInteger(),
			'absent_off' => $this->smallInteger(),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_attendance_e_attendance_activity_fkey',
            'e_attendance_activity',
            '_attendance',
            'e_attendance',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
        $this->addCommentOnTable('e_attendance_activity', $description);
		
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
	
	public function safeDown()
    {
        $this->dropTable('e_attendance_activity');
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
}
