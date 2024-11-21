<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_publication_scientific}}`.
 */
class m201130_133524_create_e_publication_scientific_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Ilmiy nashrlar to`g`risida ma`lumot';
		
        $this->createTable('e_publication_scientific', [
            'id' => $this->primaryKey(),
			'name' => $this->string(500)->notNull(),
			'keywords' => $this->string(500)->notNull(),
			'authors' => $this->string(255)->notNull(),
			'author_counts' => $this->integer(2)->notNull(),
			'source_name' => $this->string(500)->notNull(),
			'issue_year' => $this->integer()->notNull(),
			'parameter' => $this->string(500)->notNull(),
			'doi' => $this->string(255),
			'_scientific_publication_type'=>$this->string(64)->notNull(),
            '_publication_database'=>$this->string(64),
            '_locality'=>$this->string(64),
            '_country'=>$this->string(64),
            '_employee'=>$this->integer()->notNull(),
			'filename' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_h_scientific_publication_type_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_scientific_publication_type',
            'h_scientific_publication_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_publication_database_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_publication_database',
            'h_publication_database',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_locality_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_locality',
            'h_locality',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_country_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
	    $this->addCommentOnTable('e_publication_scientific', $description);
    }
	
	
    public function safeDown()
    {
        $this->dropTable('e_publication_scientific');
    }
}
