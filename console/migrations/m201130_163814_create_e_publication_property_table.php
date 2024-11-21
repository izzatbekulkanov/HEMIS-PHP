<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_publication_property}}`.
 */
class m201130_163814_create_e_publication_property_table extends Migration
{
    public function safeUp()
    {	
		$tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Intellektual muhofaza hujjatlari to`g`risida ma`lumot';
		
        $this->createTable('e_publication_property', [
            'id' => $this->primaryKey(),
			'name' => $this->string(500)->notNull(),
			'numbers' => $this->string(255)->notNull(),
			'authors' => $this->string(255)->notNull(),
			'author_counts' => $this->integer(2)->notNull(),
			'parameter' => $this->string(500),
			'property_date' => $this->date()->notNull(),
			'_patient_type'=>$this->string(64)->notNull(),
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
            'fk_h_patient_type_e_publication_property_fkey',
            'e_publication_property',
            '_patient_type',
            'h_patient_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_publication_database_e_publication_property_fkey',
            'e_publication_property',
            '_publication_database',
            'h_publication_database',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_employee_e_publication_property_fkey',
            'e_publication_property',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_locality_e_publication_property_fkey',
            'e_publication_property',
            '_locality',
            'h_locality',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_country_e_publication_scientific_fkey',
            'e_publication_property',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
	    $this->addCommentOnTable('e_publication_property', $description);
	}
	
	public function safeDown()
    {
        $this->dropTable('e_publication_property');
    }
}
