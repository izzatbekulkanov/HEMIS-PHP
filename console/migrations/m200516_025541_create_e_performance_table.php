<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_performance}}`.
 */
class m200516_025541_create_e_performance_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Baholash jadvali';
		
        $this->createTable('e_performance', [
            'id' => $this->primaryKey(),
            '_exam_schedule'=>$this->integer()->notNull(),
            '_student'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_subject'=>$this->integer()->notNull(),
	        '_employee'=>$this->integer()->notNull(),
			'_exam_type'=>$this->string(64)->notNull(),
    		'exam_name'=>$this->string(64),
            'exam_date'=>$this->date()->notNull(),
            'grade'=>$this->decimal(10, 1)->notNull(),
            'regrade'=>$this->decimal(10, 1),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'e_exam_schedule_e_performance_fkey',
            'e_performance',
            '_exam_schedule',
            'e_subject_exam_schedule',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_student_e_performance_fkey',
            'e_performance',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_education_year_e_performance_fkey',
            'e_performance',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_e_subject_e_performance_fkey',
            'e_performance',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_exam_type_e_performance_fkey',
            'e_performance',
            '_exam_type',
            'h_exam_type',
            'code',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_employee_subject_e_performance_fkey',
            'e_performance',
            '_employee',
            'e_employee',
            'id',
            'CASCADE'
        );
		
		$this->createIndex('e_performance_student_uniq',
            'e_performance',
            ['_student','_education_year', '_semester','_subject','_exam_type'],
            true);
			
        $this->addCommentOnTable('e_performance', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_performance');
    }
}
