<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_subject_schedule}}`.
 */
class m200423_085730_create_e_subject_schedule_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$description = 'Dars jadvali';

		$this->createTable('e_subject_schedule', [
            'id' => $this->primaryKey(),
			'_curriculum'=>$this->integer()->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_group'=>$this->integer()->notNull(),
			'_training_type'=>$this->string(64)->notNull(),
			'_auditorium'=>$this->integer()->notNull(),
			'_subject_topic'=>$this->integer(),
			'_week'=>$this->integer()->notNull(),
			'_employee'=>$this->integer()->notNull(),
			'_lesson_pair'=>$this->string(64)->notNull(),
			'lesson_date'=>$this->date()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_curriculum_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_group_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_training_type_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_training_type',
            'h_training_type',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_auditorium_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_auditorium',
            'e_auditorium',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_subject_topic_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_subject_topic',
            'e_curriculum_subject_topic',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_week_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_week',
            'e_curriculum_week',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_employee',
            'e_employee',
            'id',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_subject_schedule', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_subject_schedule');
    }
}
