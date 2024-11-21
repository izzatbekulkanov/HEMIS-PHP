<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%curriculum_subject_exam}}`.
 */
class m200523_132102_create_e_curriculum_subject_exam_type_table extends Migration
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
		$description = 'O`quv reja fanlarining nazorat turlari';
		
		$this->createTable('e_curriculum_subject_exam_type', [
            'id' => $this->primaryKey(),
			'_curriculum'=>$this->integer()->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_exam_type'=>$this->string(64)->notNull(),
			'max_ball'=>$this->integer()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->createIndex('e_curriculum_subject_exam_type_uniq',
              'e_curriculum_subject_exam_type',
              ['_curriculum', '_subject', '_semester', '_exam_type'],
              true);
			  
		$this->addForeignKey(
            'fk_e_curriculum_e_curriculum_subject_exam_type',
            'e_curriculum_subject_exam_type',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_curriculum_subject_exam_type',
            'e_curriculum_subject_exam_type',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_exam_type_e_curriculum_subject_exam_type',
            'e_curriculum_subject_exam_type',
            '_exam_type',
            'h_exam_type',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_curriculum_subject_exam_type', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum_subject_exam_type');
    }
}
