<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_semestr}}`.
 */
class m200318_000244_create_h_semestr_table extends Migration
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
		$description = 'O`quv semestrlari';
		$this->createTable('h_semestr', [
			'id' => $this->primaryKey(),
			'code' => $this->string(64)->notNull(),
			'name' => $this->string(256)->notNull(),
			'_curriculum'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
            'start_date'=>$this->date()->notNull(),
            'end_date'=>$this->date()->notNull(),
			'accepted' => $this->boolean()->defaultValue(false),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->createIndex('un_h_semestr_uniq',
              'h_semestr',
              ['code', '_curriculum'],
              true);
			  
		$this->addForeignKey(
            'fk_e_curriculum_h_semestr_fkey',
            'h_semestr',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_h_semestr_fkey',
            'h_semestr',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('h_semestr', $description);
	}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('h_semestr');
    }
}
