<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_project_meta}}`.
 */
class m201129_042143_create_e_project_meta_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Loyihalarning yillik moliyalashtirilishi to`g`risidagi ma`lumot';
		
        $this->createTable('e_project_meta', [
            'id' => $this->primaryKey(),
			'_project'=>$this->integer()->notNull(),
            'fiscal_year'=>$this->integer()->notNull(),
			'budget' => $this->money()->notNull(),
			'quantity_members' => $this->integer()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_project_e_project_meta_fkey',
            'e_project_meta',
            '_project',
            'e_project',
            'id',
            'RESTRICT',
            'CASCADE'
        );
			
        $this->addCommentOnTable('e_project_meta', $description);
    }
	
    public function safeDown()
    {
        $this->dropTable('e_project_meta');
    }
}
