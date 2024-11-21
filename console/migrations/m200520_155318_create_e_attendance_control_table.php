<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_attendance_control}}`.
 */
class m200520_155318_create_e_attendance_control_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Davomat kiritilish holati';
		
        $this->createTable('e_attendance_control', [
            'id' => $this->primaryKey(),
            '_subject_schedule'=>$this->integer()->notNull(),
            '_group'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_training_type'=>$this->string(64)->notNull(),
			'_employee'=>$this->integer()->notNull(),
			'_lesson_pair'=>$this->string(64)->notNull(),
			'lesson_date'=>$this->date()->notNull(),
            'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_subject_schedule_e_attendance_control_fkey',
            'e_attendance_control',
            '_subject_schedule',
            'e_subject_schedule',
            'id',
            'CASCADE'
        );
		
        $this->addForeignKey(
            'fk_e_group_e_attendance_control_fkey',
            'e_attendance_control',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		
        $this->addForeignKey(
            'fk_h_education_year_e_attendance_control_fkey',
            'e_attendance_control',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_e_subject_e_attendance_control_fkey',
            'e_attendance_control',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_training_type_e_attendance_control_fkey',
            'e_attendance_control',
            '_training_type',
            'h_training_type',
            'code',
            'CASCADE'
        );
		
        $this->addForeignKey(
            'fk_e_employee_e_attendance_control_fkey',
            'e_attendance_control',
            '_employee',
            'e_employee',
            'id',
            'CASCADE'
        );
		
        $this->createIndex('e_attendance_control_uniq',
            'e_attendance_control',
            ['_employee', '_group', '_education_year', '_semester', '_subject', '_training_type', '_lesson_pair', 'lesson_date'],
            true);
			
        $this->addCommentOnTable('e_attendance_control', $description);
	}

    public function safeDown()
    {
        $this->dropTable('e_attendance_control');
    }
}
