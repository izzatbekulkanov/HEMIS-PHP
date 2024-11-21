<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_university}}`.
 */
class m200312_205821_create_e_university_table extends Migration
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
		$description = 'OTM ma`lumotlari';
		$this->createTable('e_university', [
            'id' => $this->primaryKey(),
            'code' => $this->string(10)->notNull(),
            'tin' => $this->string(255)->notNull(),
            'name'=>$this->string(500)->notNull(),
            'address'=>$this->string(500)->notNull(),
            'contact'=>$this->string(255)->notNull(),
            '_ownership'=>$this->string(64)->notNull(),
            '_university_form'=>$this->string(64)->notNull(),
			'_translations' => 'jsonb',
			'created_at' => $this->dateTime()->notNull(),
			'updated_at' => $this->dateTime()->notNull(),
			
        ], $tableOptions);
		
		$this->createIndex('e_university_uniq',
            'e_university',
            ['code'],
            true);
			
		$this->addForeignKey(
            'fk_h_ownership',
            'e_university',
            '_ownership',
            'h_ownership',
            'code',
            'CASCADE'
        );	
		$this->addForeignKey(
            'fk_h_university_form',
            'e_university',
            '_university_form',
            'h_university_form',
            'code',
            'CASCADE'
        );	
		$this->addCommentOnTable('e_university', $description);
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_university');
    }
}
