<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_paid_contract_fee}}`.
 */
class m210414_031001_create_e_paid_contract_fee_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning shartnoma to`lovlari';
		
        $this->createTable('e_paid_contract_fee', [
            'id' => $this->primaryKey(),
			'_student_contract'=>$this->integer(),
			'_education_year'=>$this->string(64),
			'_student'=>$this->integer()->notNull(),
			'payment_number' => $this->string(255),
			'payment_date' => $this->date()->notNull(),
			'payment_type' => $this->date()->null(),
			'summa'=>$this->money()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_student_contract_e_paid_contract_fee_fkey',
            'e_paid_contract_fee',
            '_student_contract',
            'e_student_contract',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_education_year_e_paid_contract_fee_fkey',
            'e_paid_contract_fee',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_student_e_paid_contract_fee_fkey',
            'e_paid_contract_fee',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_student_contract', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_paid_contract_fee');
    }
	
}
