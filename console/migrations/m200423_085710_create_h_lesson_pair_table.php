<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_lesson_pair}}`.
 */
class m200423_085710_create_h_lesson_pair_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$description = 'Juftliklar (paralar)';
		
		$this->createTable('h_lesson_pair', [
            'id' => $this->primaryKey(),
			'code' => $this->string(64)->notNull(),
			'name' => $this->string(256)->notNull(),
			'start_time' => $this->string(10)->notNull(),
			'end_time' => $this->string(10)->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->createIndex('un_h_lesson_pair_uniq',
              'h_lesson_pair',
              ['code', '_education_year'],
              true);
			  
		$this->addForeignKey(
            'fk_h_education_year_h_lesson_pair_fkey',
            'h_lesson_pair',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('h_lesson_pair', $description);
		
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('h_lesson_pair');
    }
}
