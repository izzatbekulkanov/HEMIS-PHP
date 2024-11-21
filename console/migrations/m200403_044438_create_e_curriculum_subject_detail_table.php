<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_curriculum_subject_detail}}`.
 */
class m200403_044438_create_e_curriculum_subject_detail_table extends Migration
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
		$description = 'O`quv reja fanlari sillabuslari';
		
		$this->createTable('e_curriculum_subject_detail', [
            'id' => $this->primaryKey(),
			'_curriculum'=>$this->integer()->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_training_type'=>$this->string(64)->notNull(),
			'academic_load'=>$this->integer()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->createIndex('e_curriculum_subject_detail_uniq',
              'e_curriculum_subject_detail',
              ['_curriculum', '_subject', '_semester', '_training_type'],
              true);
			  
		$this->addForeignKey(
            'fk_e_curriculum_e_curriculum_subject_detail',
            'e_curriculum_subject_detail',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_curriculum_subject_detail',
            'e_curriculum_subject_detail',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_training_type_e_curriculum_subject_detail',
            'e_curriculum_subject_detail',
            '_training_type',
            'h_training_type',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_curriculum_subject_detail', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum_subject_detail');
    }
}
