<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_curriculum_subject_topic}}`.
 */
class m200410_120527_create_e_curriculum_subject_topic_table extends Migration
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
		$description = 'O`quv reja fanlari mavzulari';
		
		$this->createTable('e_curriculum_subject_topic', [
            'id' => $this->primaryKey(),
			'name' => $this->string(500)->notNull(),
			'_curriculum'=>$this->integer()->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_training_type'=>$this->string(64)->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_curriculum_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		
		/*$this->addForeignKey(
            'fk_h_semester_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_semester',
            'h_semestr',
            'code',
            'CASCADE'
        );*/
		
		$this->addForeignKey(
            'fk_h_training_type_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_training_type',
            'h_training_type',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_curriculum_subject_topic', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum_subject_topic');
    }
}
