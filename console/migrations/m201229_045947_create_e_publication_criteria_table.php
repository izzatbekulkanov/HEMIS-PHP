<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_publication_criteria}}`.
 */
class m201229_045947_create_e_publication_criteria_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Nashr ishlarini baholash mezonlari';
		
        $this->createTable('e_publication_criteria', [
            'id' => $this->primaryKey(),
			'_education_year'=>$this->string(64)->notNull(),
			'_publication_type_table'=>$this->string(64)->notNull(),
			'_publication_methodical_type'=>$this->string(64),
			'_publication_scientific_type'=>$this->string(64),
			'_publication_property_type'=>$this->string(64),
			'_in_publication_database'=>$this->integer(3)->defaultValue(0),
			'mark_value'=>$this->integer()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_education_year_e_publication_criteria_fkey',
            'e_publication_criteria',
            '_education_year',
            'e_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_methodical_publication_type_e_publication_criteria_fkey',
            'e_publication_criteria',
            '_publication_methodical_type',
            'h_methodical_publication_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_scientific_publication_type_e_publication_criteria_fkey',
            'e_publication_criteria',
            '_publication_scientific_type',
            'h_scientific_publication_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_patient_type_e_publication_criteria_fkey',
            'e_publication_criteria',
            '_publication_property_type',
            'h_patient_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addCommentOnTable('e_publication_criteria', $description);
    }
	
    public function safeDown()
    {
        $this->dropTable('e_publication_criteria');
    }
}
