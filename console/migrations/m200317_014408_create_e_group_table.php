<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_group}}`.
 */
class m200317_014408_create_e_group_table extends Migration
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
		$description = 'OTM guruhlari';
		$this->createTable('e_group', [
            'id' => $this->primaryKey(),
			'name' => $this->string(256)->notNull(),
			'_department'=>$this->integer()->notNull(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            '_specialty'=>$this->string(64)->notNull(),
            '_curriculum'=>$this->integer()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		$this->addForeignKey(
            'fk_e_department_fkey',
            'e_group',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_g_education_type_fkey',
            'e_group',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_g_education_form_fkey',
            'e_group',
            '_education_form',
            'h_education_form',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_g_specialty_fkey',
            'e_group',
            '_specialty',
            'e_specialty',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_g_curriculum_fkey',
            'e_group',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addCommentOnTable('e_group', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_group');
    }
}
