<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_publication_methodical}}`.
 */
class m201130_123605_create_e_publication_methodical_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Uslubiy nashrlar to`g`risida ma`lumot';
		
        $this->createTable('e_publication_methodical', [
            'id' => $this->primaryKey(),
			'name' => $this->string(500)->notNull(),
			'authors' => $this->string(255)->notNull(),
			'author_counts' => $this->integer(2)->notNull(),
			'publisher' => $this->string(500)->notNull(),
			'issue_year' => $this->integer()->notNull(),
			'source_name' => $this->string(500)->notNull(),
			'parameter' => $this->string(500)->notNull(),
			'_methodical_publication_type'=>$this->string(64)->notNull(),
            '_publication_database'=>$this->string(64),
            '_employee'=>$this->integer()->notNull(),
			'filename' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_h_methodical_publication_type_e_publication_methodical_fkey',
            'e_publication_methodical',
            '_methodical_publication_type',
            'h_methodical_publication_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_publication_database_e_publication_methodical_fkey',
            'e_publication_methodical',
            '_publication_database',
            'h_publication_database',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_publication_methodical_fkey',
            'e_publication_methodical',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
	    $this->addCommentOnTable('e_publication_methodical', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_publication_methodical');
    }
}
