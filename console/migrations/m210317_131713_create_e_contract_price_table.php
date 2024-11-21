<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_contract_price}}`.
 */
class m210317_131713_create_e_contract_price_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'To`lov-shartnoma miqdorlari';
		
        $this->createTable('e_contract_price', [
            'id' => $this->primaryKey(),
            '_department'=>$this->integer()->notNull(),
			'_specialty'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64),
			'_education_type'=>$this->string(64),
			'_education_form'=>$this->string(64)->notNull(),
			'_country'=>$this->string(64)->null(),
			'_student_type'=>$this->string(64)->notNull(),
			'_have_access_certificate'=>$this->boolean()->defaultValue(false),
			'_minimum_wage'=>$this->integer(),
			'_contract_currency'=>$this->string(64)->notNull(),
			'coefficient'=>$this->decimal(10,1),
			'summa'=>$this->money()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'e_department_e_contract_price_fkey',
            'e_contract_price',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_e_contract_price_fkey',
            'e_contract_price',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_education_year_e_contract_price_fkey',
            'e_contract_price',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_type_e_contract_price_fkey',
            'e_contract_price',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_contract_price_fkey',
            'e_contract_price',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_citizenship_type_e_contract_price_fkey',
            'e_contract_price',
            '_student_type',
            'h_citizenship_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_minimum_wage_e_contract_price_fkey',
            'e_contract_price',
            '_minimum_wage',
            'e_minimum_wage',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_project_currency_e_contract_price_fkey',
            'e_contract_price',
            '_contract_currency',
            'h_project_currency',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
	    $this->addCommentOnTable('e_contract_price', $description);
    }
	
    public function safeDown()
    {
        $this->dropTable('e_contract_price');
    }
}
