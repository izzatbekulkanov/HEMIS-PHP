<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_contract_type}}`.
 */
class m210325_121507_create_e_student_contract_type_table extends Migration
{
    public function safeUp()
    {
        \common\models\system\SystemClassifier::createClassifiersTables($this);
		$tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning shartnoma summasi turi bo`yicha tanlovlari';
		
        $this->createTable('e_student_contract_type', [
            'id' => $this->primaryKey(),
			'_specialty'=>$this->integer()->notNull(),
            '_student'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64),
			'_education_form'=>$this->string(64)->notNull(),
			'_contract_summa_type'=>$this->string(64),
			'contract_form_type'=>$this->string(64),
			'_created_self' => $this->boolean()->defaultValue(false),
			'contract_status' => $this->string(64),
			'position' => $this->integer(3)->defaultValue(0),
			'active' => $this->boolean()->defaultValue(true),
			'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
       $this->addForeignKey(
            'fk_e_specialty_e_student_contract_type_fkey',
            'e_student_contract_type',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_student_e_student_contract_type_fkey',
            'e_student_contract_type',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_education_year_e_student_contract_type_fkey',
            'e_student_contract_type',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_form_e_student_contract_type_fkey',
            'e_student_contract_type',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_contract_summa_type_e_student_contract_type_fkey',
            'e_student_contract_type',
            '_contract_summa_type',
            'h_contract_summa_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		
	    $this->addCommentOnTable('e_student_contract_type', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_student_contract_type');
    }
}
