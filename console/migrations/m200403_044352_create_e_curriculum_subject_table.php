<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_curriculum_subject}}`.
 */
class m200403_044352_create_e_curriculum_subject_table extends Migration
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
		$description = 'O`quv reja fanlari';
		
		$this->createTable('e_curriculum_subject', [
            'id' => $this->primaryKey(),
			'_curriculum'=>$this->integer()->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_curriculum_subject_block'=>$this->string(64),
			'_semester'=>$this->string(64)->notNull(),
			'_subject_type'=>$this->string(64),
			'_rating_grade'=>$this->string(64),
			'_exam_finish'=>$this->string(64),
			'total_acload'=>$this->integer(),
			'credit'=>$this->integer(),
            'reorder' => $this->boolean()->defaultValue(true),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->createIndex('curriculum_uniq',
              'e_curriculum_subject',
              ['_curriculum', '_subject', '_semester'],
              true);
			  
		$this->addForeignKey(
            'fk_e_curriculum_curriculum_subject',
            'e_curriculum_subject',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_subject_curriculum_subject',
            'e_curriculum_subject',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_curriculum_subject_block_curriculum_subject',
            'e_curriculum_subject',
            '_curriculum_subject_block',
            'h_subject_block',
            'code',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_subject_type_curriculum_subject',
            'e_curriculum_subject',
            '_subject_type',
            'h_subject_type',
            'code',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_rating_grade_curriculum_subject',
            'e_curriculum_subject',
            '_rating_grade',
            'h_rating_grade',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_exam_finish_curriculum_subject',
            'e_curriculum_subject',
            '_exam_finish',
            'h_exam_finish',
            'code',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_curriculum_subject', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum_subject');
    }
}
