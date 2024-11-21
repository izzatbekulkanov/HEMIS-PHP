<?php

use yii\db\Migration;

/**
 * Class m201230_191517_alter_table_publication_methodical_criteria
 */
class m201230_191517_alter_table_publication_methodical_criteria extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('e_publication_methodical', 'source_name', $this->string(500)->null());
		$this->addColumn('e_publication_methodical', 'certificate_number', $this->string(64)->null());
        $this->addColumn('e_publication_methodical', 'certificate_date', $this->date()->null());
		$this->addColumn('e_publication_criteria', 'exist_certificate', $this->integer(3)->defaultValue(0));
		
    }

    public function safeDown()
    {
        $this->alterColumn('e_publication_methodical', 'source_name', $this->string(500)->notNull());
		$this->dropColumn('e_publication_methodical', 'certificate_number');
        $this->dropColumn('e_publication_methodical', 'certificate_date');
        $this->dropColumn('e_publication_criteria', 'exist_certificate');
	}
}
