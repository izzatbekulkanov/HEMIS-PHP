<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_auditorium}}`.
 */
class m200423_083209_create_e_auditorium_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$description = 'OTMdagi auditoriyalar ro`yxati';
		
		$this->createTable('e_auditorium', [
            'code' => $this->primaryKey(),
			'name' => $this->string(256)->notNull(),
			'_building' => $this->integer()->notNull(),
			'_auditorium_type' => $this->string(64)->notNull(),
			'volume' => $this->integer()->notNull(),
	        'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_h_building_e_auditorium_fkey',
            'e_auditorium',
            '_building',
            'h_building',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_auditorium_type_e_auditorium_fkey',
            'e_auditorium',
            '_auditorium_type',
            'h_auditorium_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_auditorium', $description);	
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_auditorium');
    }
}
