<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_performance_control}}`.
 */
class m200520_155344_create_e_performance_control_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Davomat kiritilish holati';
		
        $this->createTable('e_performance_control', [
            'id' => $this->primaryKey(),
            '_exam_schedule'=>$this->integer()->notNull(),
            '_group'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_employee'=>$this->integer()->notNull(),
			'_lesson_pair'=>$this->string(64)->notNull(),
			'_exam_type'=>$this->string(64)->notNull(),
    		'exam_name'=>$this->string(64)->notNull(),
            'exam_date'=>$this->date()->notNull(),
            'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'e_exam_schedule_e_performance_control_fkey',
            'e_performance_control',
            '_exam_schedule',
            'e_subject_exam_schedule',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_group_e_performance_control_fkey',
            'e_performance_control',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_education_year_e_performance_control_fkey',
            'e_performance_control',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_e_subject_e_performance_control_fkey',
            'e_performance_control',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_employee_subject_e_performance_control_fkey',
            'e_performance_control',
            '_employee',
            'e_employee',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_exam_type_e_performance_control_fkey',
            'e_performance_control',
            '_exam_type',
            'h_exam_type',
            'code',
            'CASCADE'
        );
		
		$this->createIndex('e_performance_control_uniq',
            'e_performance_control',
            ['_employee','_group', '_education_year','_semester', '_subject', '_exam_type', 'exam_name', '_lesson_pair', 'exam_date'],
            true);
        $this->addCommentOnTable('e_performance_control', $description);
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_performance_control');
    }
}
