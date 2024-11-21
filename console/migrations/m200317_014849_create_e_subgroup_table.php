<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_subgroup}}`.
 */
class m200317_014849_create_e_subgroup_table extends Migration
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
		$description = 'Kichik guruh ma`lumotlari';
		
		$this->createTable('e_subgroup', [
           'id' => $this->primaryKey(),
			'name' => $this->string(256)->notNull(),
			'_group'=>$this->integer()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		$this->addForeignKey(
            'fk_es_group_fkey',
            'e_subgroup',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		$this->addCommentOnTable('e_subgroup', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_subgroup');
    }
}
