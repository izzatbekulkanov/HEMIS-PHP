<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_task_activity}}`.
 */
class m200911_070800_create_e_student_task_activity_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning topshiriqlarga bergan javoblari';
		
        $this->createTable('e_student_task_activity', [
            'id' => $this->primaryKey(),
		    '_subject_task_student'=>$this->integer()->notNull(),
		    '_subject_task'=>$this->integer()->notNull(),
		    '_curriculum'=>$this->integer()->notNull(),
            '_subject'=>$this->integer()->notNull(),
        	'_training_type'=>$this->string(64)->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
	        '_student'=>$this->integer()->notNull(),
	        '_employee'=>$this->integer(),
			'send_date'=>$this->dateTime()->notNull(),
			'filename' => 'jsonb',
			'comment' => $this->text()->notNull(),
			'attempt_count' => $this->integer()->notNull(),
			'mark' => $this->decimal(10, 1),
			'marked_date' => $this->dateTime(),
			'marked_comment' => $this->text(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_subject_task_student_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_subject_task_student',
            'e_subject_task_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_task_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_subject_task',
            'e_subject_task',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_training_type_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_student_e_student_task_activity_fkey',
            'e_student_task_activity',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
		
		$this->addCommentOnTable('e_student_task_activity', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_task_activity');
    }
	
}
