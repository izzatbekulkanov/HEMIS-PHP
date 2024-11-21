<?php

use yii\db\Migration;

/**
 * Class m210111_165955_alter_table_doctorate_student_defense
 */
class m210111_165955_alter_table_doctorate_student_defense extends Migration
{
    
    public function safeUp()
    {	
		$this->addColumn('e_dissertation_defense', 'scientific_council', $this->string(1000)->null());
		$this->addColumn('e_dissertation_defense', '_second_specialty_id', $this->integer()->null());
		$this->addColumn('e_doctorate_student', '_second_specialty_id', $this->integer()->null());
		$this->addForeignKey(
            'fk_e_specialty_e_dissertation_defense_second_fkey',
            'e_dissertation_defense',
            '_second_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_e_doctorate_student_second_fkey',
            'e_doctorate_student',
            '_second_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_e_specialty_e_dissertation_defense_second_fkey', 'e_dissertation_defense');
        $this->dropForeignKey('fk_e_specialty_e_doctorate_student_second_fkey', 'e_doctorate_student');
        $this->dropColumn('e_dissertation_defense', 'scientific_council');
        $this->dropColumn('e_dissertation_defense', '_second_specialty_id');
        $this->dropColumn('e_doctorate_student', '_second_specialty_id');
    }

    
}
