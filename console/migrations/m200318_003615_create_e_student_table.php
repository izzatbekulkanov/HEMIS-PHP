<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student}}`.
 */
class m200318_003615_create_e_student_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Talaba shaxsiy ma`lumotlari';
		
		$this->createTable('e_student', [
            'id' => $this->primaryKey(),
			'first_name' => $this->string(100)->notNull(),
			'second_name' => $this->string(100)->notNull(),
			'third_name' => $this->string(100),
			'birth_date' => $this->date()->notNull(),
            'student_id_number'=>$this->string(14),
            'passport_number'=>$this->string(10)->notNull(),
            'passport_pin'=>$this->string(15)->notNull(),
            '_gender'=>$this->string(64)->notNull(),
			'_nationality'=>$this->string(64)->notNull(),
			'_citizenship'=>$this->string(64)->notNull(),
			'_country'=>$this->string(64)->notNull(),
			'_province'=>$this->string(64)->notNull(),
			'_district'=>$this->string(64)->notNull(),
            '_accommodation'=>$this->string(64)->notNull(),
            '_social_category'=>$this->string(64)->notNull(),
			'home_address'=>$this->string(255)->notNull(),
			'current_address'=>$this->string(255)->notNull(),
			'year_of_enter'=>$this->integer(4)->notNull(),
            'other'=>$this->string(1024),
            'image' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_h_gender_student_fkey',
            'e_student',
            '_gender',
            'h_gender',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_nationality_student_fkey',
            'e_student',
            '_nationality',
            'h_nationality',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_citizenship_student_fkey',
            'e_student',
            '_citizenship',
            'h_citizenship_type',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_country_student_fkey',
            'e_student',
            '_country',
            'h_country',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_province_student_fkey',
            'e_student',
            '_province',
            'h_soato',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_district_student_fkey',
            'e_student',
            '_district',
            'h_soato',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_accommodation_student_fkey',
            'e_student',
            '_accommodation',
            'h_accommodation',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_social_category_student_fkey',
            'e_student',
            '_social_category',
            'h_social_category',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_student', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student');
    }
}
