<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%s_subject_exam_schedule}}`.
 */
class m200430_042537_create_e_subject_exam_schedule_table extends Migration
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
        $description = 'Nazorat jadvali';
        $this->createTable('e_subject_exam_schedule', [
            'id' => $this->primaryKey(),
            '_curriculum'=>$this->integer()->notNull(),
            '_subject'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_group'=>$this->integer()->notNull(),
           '_exam_type'=>$this->string(64)->notNull(),
            '_auditorium'=>$this->integer()->notNull(),
			'_week'=>$this->integer()->notNull(),
			'_employee'=>$this->integer()->notNull(),
			'_lesson_pair'=>$this->string(64)->notNull(),
			'exam_name'=>$this->string(64)->notNull(),
            'exam_date'=>$this->date()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'e_subject_exam_schedule_curriculum_fkey',
            'e_subject_exam_schedule',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'e_subject_exam_schedule_subject_fkey',
            'e_subject_exam_schedule',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'e_subject_exam_schedule_education_year_fkey',
            'e_subject_exam_schedule',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'e_subject_exam_schedule_group_fkey',
            'e_subject_exam_schedule',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'e_subject_exam_schedule_exam_type_fkey',
            'e_subject_exam_schedule',
            '_exam_type',
            'h_exam_type',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_auditorium_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_auditorium',
            'e_auditorium',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_curriculum_week_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_week',
            'e_curriculum_week',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_employee_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_employee',
            'e_employee',
            'id',
            'CASCADE'
        );
	    $this->addCommentOnTable('e_subject_exam_schedule', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_subject_exam_schedule');
    }
}
