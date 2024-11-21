<?php

use yii\db\Migration;

/**
 * Class m201010_081743_create_table_e_subject_topic_question
 */
class m201010_081743_create_e_subject_topic_question extends Migration
{
	public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'O`qituvchilarning fan bo`yicha kiritgan test savollari';
		
        $this->createTable('e_subject_topic_question', [
            'id' => $this->primaryKey(),
			'name' => $this->text()->notNull(),
			'content' => $this->text()->notNull(),
			'content_r' => $this->text()->notNull(),
			'answers' => 'jsonb',
			'_answer' => 'jsonb',
            '_curriculum'=>$this->integer()->notNull(),
            '_subject'=>$this->integer()->notNull(),
            '_language'=>$this->string(64)->notNull(),
			'_training_type'=>$this->string(64)->notNull(),
			'_subject_topic'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
	        '_employee'=>$this->integer()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_curriculum_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_language_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_language',
            'h_language',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_training_type_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_subject_topic_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_subject_topic',
            'e_curriculum_subject_topic',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_subject_topic_question_fkey',
            'e_subject_topic_question',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
			
        $this->addCommentOnTable('e_subject_topic_question', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_subject_topic_question');
    }
   
}
