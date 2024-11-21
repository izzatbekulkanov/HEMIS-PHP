<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%curriculum_unit_fixed}}`.
 */
class m200330_073411_create_e_curriculum_subject_block_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$description = 'O`quv reja bloklari';
		
		$this->createTable('e_curriculum_subject_block', [
            'id' => $this->primaryKey(),
			'code' => $this->string(64)->notNull(),
			'_curriculum'=>$this->integer()->notNull(),
			'_subject_block'=>$this->string(64)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_curriculum_curriculum_subject_block',
            'e_curriculum_subject_block',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_curriculum_unit_curriculum_subject_block',
            'e_curriculum_subject_block',
            '_subject_block',
            'h_subject_block',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_curriculum_subject_block', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum_subject_block');
    }
}
