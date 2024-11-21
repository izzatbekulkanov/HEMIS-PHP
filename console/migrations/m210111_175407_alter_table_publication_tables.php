<?php

use yii\db\Migration;

/**
 * Class m210111_175407_alter_table_publication_tables
 */
class m210111_175407_alter_table_publication_tables extends Migration
{
	public function safeUp()
    {
		$this->addColumn('e_publication_methodical', '_language', $this->string(64));
        $this->addColumn('e_publication_scientific', '_language', $this->string(64));
        $this->addColumn('e_publication_property', '_language', $this->string(64));
		
		$this->addForeignKey(
            'fk_h_language_e_publication_methodical_fkey',
            'e_publication_methodical',
            '_language',
            'h_language',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_language_e_publication_scientific_fkey',
            'e_publication_scientific',
            '_language',
            'h_language',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_language_e_publication_property_fkey',
            'e_publication_property',
            '_language',
            'h_language',
            'code',
            'NO ACTION',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_publication_methodical', '_language');
        $this->dropColumn('e_publication_scientific', '_language');
        $this->dropColumn('e_publication_property', '_language');
    }
}
