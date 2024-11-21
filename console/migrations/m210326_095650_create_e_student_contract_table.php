<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_contract}}`.
 */
class m210326_095650_create_e_student_contract_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning shartnomalari';
		
        $this->createTable('e_student_contract', [
            'id' => $this->primaryKey(),
			'number' => $this->string(64),
			'date' => $this->date()->notNull(),
			'summa'=>$this->money()->notNull(),
			'_student_contract_type'=>$this->integer(),
			'_contract_summa_type'=>$this->string(64),
			'contract_form_type'=>$this->string(64),
			'_education_year'=>$this->string(64),
			'_student'=>$this->integer()->notNull(),
			'_specialty'=>$this->integer()->notNull(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            'university_code' => $this->string(10),
			'rector' => $this->string(255),
			'mailing_address' => $this->text(),
			'bank_details' => $this->text(),
			'contract_status' => $this->string(64),
			'customer' => $this->string(255),
			'position' => $this->integer(3)->defaultValue(0),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_student_contract_type_e_student_contract_fkey',
            'e_student_contract',
            '_student_contract_type',
            'e_student_contract_type',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_contract_summa_type_e_student_contract_fkey',
            'e_student_contract',
            '_contract_summa_type',
            'h_contract_summa_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_education_year_e_student_contract_fkey',
            'e_student_contract',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_student_e_student_contract_fkey',
            'e_student_contract',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_e_student_contract_fkey',
            'e_student_contract',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
	    $this->addForeignKey(
            'fk_h_education_type_e_student_contract_fkey',
            'e_student_contract',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_form_e_student_contract_fkey',
            'e_student_contract',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addCommentOnTable('e_student_contract', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_student_contract');
    }
}