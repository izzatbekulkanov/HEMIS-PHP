<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_rating_grade}}`.
 */
class m200403_044326_create_h_rating_grade_table extends Migration
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
		
		$description = 'O`zlashtirish qaydnomasi shakllari';
		$this->createTable('h_rating_grade', [
            'code' => $this->string(64)->notNull()->unique(),
			'name' => $this->string(256)->notNull(),
			'template' => $this->string(256)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		$this->addPrimaryKey('pk_h_rating_grade_code', 'h_rating_grade', ['code']);
		$this->addCommentOnTable('h_semestr', $description);
    }

    public function safeDown()
    {
        $this->dropTable('h_rating_grade');
    }
}
