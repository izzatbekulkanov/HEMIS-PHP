<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_department}}`.
 */
class m200314_123811_create_e_department_table extends Migration
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
		$description = 'OTM tuzilma nomlari';
		
		$this->createTable('e_department', [
			'id' => $this->primaryKey(),
            'code' => $this->string(64)->notNull()->unique(),
			'name' => $this->string(256)->notNull(),
			'_university'=>$this->integer()->notNull(),
            '_structure_type'=>$this->string(64)->notNull(),
			'parent'=>$this->integer(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		$this->createIndex(
            'fk_e_university_id',
            'e_department',
            '_university'
        );
        $this->addForeignKey(
            'fk_e_university_id',
            'e_department',
            '_university',
            'e_university',
            'id',
            'CASCADE'
        );
        $this->createIndex(
            'e_department_structure_type_fkey',
            'e_department',
            '_structure_type'
        );
        $this->addForeignKey(
            'e_department_structure_type_fkey',
            'e_department',
            '_structure_type',
            'h_structure_type',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_department', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_department');
    }
}
