<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_building}}`.
 */
class m200423_083136_create_h_building_table extends Migration
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
		$description = 'OTMdagi binolar ro`yxati';
		
		$this->createTable('h_building', [
            'code' => $this->primaryKey(),
			'name' => $this->string(256)->notNull(),
			'address'=>$this->string(500)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		$this->addCommentOnTable('h_building', $description);	
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('h_building');
    }
}
