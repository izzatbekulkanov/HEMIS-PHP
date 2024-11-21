<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_publication_author_meta}}`.
 */
class m201223_185025_create_e_publication_author_meta_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Ilmiy nashrlarning mualliflari to`g`risida ma`lumot';
		
        $this->createTable('e_publication_author_meta', [
            'id' => $this->primaryKey(),
			'_employee'=>$this->integer()->notNull(),
			'is_main_author'=>$this->integer()->notNull(),
			'_publication_type_table'=>$this->string(64)->notNull(),
			'_publication_methodical'=>$this->integer(),
			'_publication_scientific'=>$this->integer(),
			'_publication_property'=>$this->integer(),
			'is_checked_by_author'=>$this->boolean(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_employee_e_publication_author_meta_fkey',
            'e_publication_author_meta',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_publication_methodical_e_publication_author_meta_fkey',
            'e_publication_author_meta',
            '_publication_methodical',
            'e_publication_methodical',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_publication_scientific_e_publication_author_meta_fkey',
            'e_publication_author_meta',
            '_publication_scientific',
            'e_publication_scientific',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_publication_property_e_publication_author_meta_fkey',
            'e_publication_author_meta',
            '_publication_property',
            'e_publication_property',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->createIndex('e_publication_methodical_author_meta_uniq',
            'e_publication_author_meta',
            ['_employee', '_publication_type_table','_publication_methodical'],
            true);
		$this->createIndex('e_publication_scientific_author_meta_uniq',
            'e_publication_author_meta',
            ['_employee', '_publication_type_table','_publication_scientific'],
            true);
		$this->createIndex('e_publication_property_author_meta_uniq',
            'e_publication_author_meta',
            ['_employee', '_publication_type_table','_publication_property'],
            true);
		$this->addCommentOnTable('e_publication_author_meta', $description);
    }
	
    public function safeDown()
    {
        $this->dropTable('e_publication_author_meta');
    }
}
