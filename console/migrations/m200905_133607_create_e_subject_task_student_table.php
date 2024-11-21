<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_subject_task_student}}`.
 */
class m200905_133607_create_e_subject_task_student_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Fan bo`yicha talabalarga biriktirilgan vazifalar';
		
        $this->createTable('e_subject_task_student', [
            'id' => $this->primaryKey(),
		    '_subject_task'=>$this->integer()->notNull(),
		    '_curriculum'=>$this->integer()->notNull(),
            '_subject'=>$this->integer()->notNull(),
        	'_training_type'=>$this->string(64)->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
	        '_employee'=>$this->integer()->notNull(),
			'_student'=>$this->integer()->notNull(),
			'_group'=>$this->integer()->notNull(),
			'_task_status'=>$this->string(64)->defaultValue(11),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'published_at' => $this->dateTime(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_subject_task_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_subject_task',
            'e_subject_task',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_training_type_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_student_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_group_e_subject_task_student_fkey',
            'e_subject_task_student',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->createIndex('e_subject_task_student_uniq',
            'e_subject_task_student',
            ['_subject_task','_student','_education_year','_semester','_subject'],
            true);	

		$this->addCommentOnTable('e_subject_task_student', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_subject_task_student');
    }
	
}
