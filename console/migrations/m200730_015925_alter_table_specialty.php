<?php

use yii\db\Migration;

/**
 * Class m200730_015925_alter_table_specialty
 */
class m200730_015925_alter_table_specialty extends Migration
{
   public function safeUp()
   {
		$this->dropPrimaryKey('pk_e_special_code', 'e_specialty');
		$this->createIndex('e_special_department_uniq',
            'e_specialty',
            ['code','_department'],
            true);
		$this->addColumn('e_specialty', 'id', $this->primaryKey());
		$this->addColumn('e_specialty', '_type', $this->string(64)->defaultValue(11));
        
		$this->addForeignKey(
            'fk_e_specialty_type',
            'e_specialty',
            '_type',
            'h_locality_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
		
		$this->dropForeignKey('fk_c_specialty_fkey','e_curriculum');
		$this->dropForeignKey('fk_g_specialty_fkey','e_group');
		$this->dropForeignKey('fk_e_specialty_student_meta_fkey', 'e_student_meta');
		$this->dropForeignKey('fk_e_specialty_e_student_diploma_fkey', 'e_student_diploma');
		//$this->dropIndex('e_specialty_code_key', 'e_specialty');
	}
	

    public function safeDown()
    {
        $this->dropColumn('e_specialty', '_type');
        $this->dropColumn('e_specialty', 'id');
		$this->dropPrimaryKey('pk_e_special_code', 'e_specialty');
		$this->dropIndex('e_special_department_uniq', 'e_specialty');
		
		$this->addForeignKey(
            'fk_c_specialty_fkey',
            'e_curriculum',
            '_specialty',
            'e_specialty',
            'code',
            'RESTRICT',
            'CASCADE'
        );
	    $this->addForeignKey(
            'fk_g_specialty_fkey',
            'e_group',
            '_specialty',
            'e_specialty',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_student_meta_fkey',
            'e_student_meta',
            '_specialty',
            'e_specialty',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_e_student_diploma_fkey',
            'e_student_diploma',
            '_specialty',
            'e_specialty',
            'code',
            'NO ACTION',
            'CASCADE'
        );
	}
}
