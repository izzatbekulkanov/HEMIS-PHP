<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_increased_contract_coefficient}}`.
 */
class m210320_170604_create_e_increased_contract_coefficient_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Oshirilgan shartnoma summalarini hisoblash koeffisientlari';
		
        $this->createTable('e_increased_contract_coefficient', [
            'id' => $this->primaryKey(),
            '_department'=>$this->integer()->notNull(),
			'_specialty'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_contract_type'=>$this->integer(),
			'coefficient'=>$this->decimal(10,1),
			'position' => $this->integer(3)->defaultValue(0),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'e_department_e_increased_contract_coefficient_fkey',
            'e_increased_contract_coefficient',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_e_increased_contract_coefficient_fkey',
            'e_increased_contract_coefficient',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_education_year_e_increased_contract_coefficient_fkey',
            'e_increased_contract_coefficient',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_contract_type_e_increased_contract_coefficient_fkey',
            'e_increased_contract_coefficient',
            '_contract_type',
            'e_contract_type',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
		
		
		
	    $this->addCommentOnTable('e_increased_contract_coefficient', $description);
    }
	
    public function safeDown()
    {
        $this->dropTable('e_increased_contract_coefficient');
    }
}
