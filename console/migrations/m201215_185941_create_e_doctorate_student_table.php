<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_doctorate_student}}`.
 */
class m201215_185941_create_e_doctorate_student_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Muassasadagi doktorantlar to`g`risida ma`lumot';
		
		\common\models\system\SystemClassifier::createClassifiersTables($this);
		
        $this->createTable('e_doctorate_student', [
            'id' => $this->primaryKey(),
			'first_name'=>$this->string(100)->notNull(),
			'second_name'=>$this->string(100)->notNull(),
			'third_name'=>$this->string(100),
			'passport_number'=>$this->string(14),
			'passport_pin'=>$this->string(20),
			'birth_date' => $this->date()->notNull(),
			'dissertation_theme'=>$this->string(500)->notNull(),
			'home_address'=>$this->string(255)->notNull(),
			'accepted_date'=>$this->date()->notNull(),
			'student_id_number'=>$this->string(14),
			'_science_branch_id' => $this->string(36)->notNull(),
			'_specialty_id' => $this->integer()->notNull(),
			'_payment_form'=>$this->string(64)->notNull(),
			'_citizenship'=>$this->string(64)->notNull(),
			'_nationality'=>$this->string(64)->notNull(),
			'_gender'=>$this->string(64)->notNull(),
			'_country'=>$this->string(64),
			'_province'=>$this->string(64),
			'_district'=>$this->string(64),
			'_doctoral_student_type'=>$this->string(64)->notNull(),
			'_doctorate_student_status'=>$this->string(64)->notNull(),
			'_level'=>$this->string(64),
			'_department'=>$this->integer()->notNull(),
            'image' => 'jsonb',
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_specialty_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_science_branch_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_science_branch_id',
            'h_science_branch',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_payment_form_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_payment_form',
            'h_payment_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_citizenship_type_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_citizenship',
            'h_citizenship_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_nationality_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_nationality',
            'h_nationality',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_gender_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_gender',
            'h_gender',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_country_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_province_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_province',
            'h_soato',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_district_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_district',
            'h_soato',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_doctoral_student_type_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_doctoral_student_type',
            'h_doctoral_student_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_doctorate_student_status_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_doctorate_student_status',
            'h_doctorate_student_status',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_department_e_doctorate_student_fkey',
            'e_doctorate_student',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
        $this->addCommentOnTable('e_doctorate_student', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_doctorate_student');
    }
}