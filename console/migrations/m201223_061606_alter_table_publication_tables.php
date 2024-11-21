<?php

use yii\db\Migration;

/**
 * Class m201223_061606_alter_table_publication_tables
 */
class m201223_061606_alter_table_publication_tables extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_publication_methodical', 'is_checked', $this->boolean());
        $this->addColumn('e_publication_scientific', 'is_checked', $this->boolean());
        $this->addColumn('e_publication_property', 'is_checked', $this->boolean());
		$this->addColumn('e_publication_methodical', 'is_checked_date', $this->dateTime()->null());
        $this->addColumn('e_publication_scientific', 'is_checked_date', $this->dateTime()->null());
        $this->addColumn('e_publication_property', 'is_checked_date', $this->dateTime()->null());
		
		$this->addColumn('e_publication_methodical', '_education_year', $this->string(64));
        $this->addColumn('e_publication_scientific', '_education_year', $this->string(64));
        $this->addColumn('e_publication_property', '_education_year', $this->string(64));
		
		$this->addForeignKey(
            'fk_e_education_year_e_publication_methodical_fkey',
            'e_publication_methodical',
            '_education_year',
            'e_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_education_year_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_education_year',
            'e_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_education_year_e_publication_property_fkey',
            'e_publication_property',
            '_education_year',
            'e_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
    }


    public function safeDown()
    {
        $this->dropColumn('e_publication_methodical', 'is_checked');
        $this->dropColumn('e_publication_scientific', 'is_checked');
        $this->dropColumn('e_publication_property', 'is_checked');
		$this->dropColumn('e_publication_methodical', '_education_year');
        $this->dropColumn('e_publication_scientific', '_education_year');
        $this->dropColumn('e_publication_property', '_education_year');
    }
}
