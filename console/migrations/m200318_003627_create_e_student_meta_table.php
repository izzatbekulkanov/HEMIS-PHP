<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_meta}}`.
 */
class m200318_003627_create_e_student_meta_table extends Migration
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
		$description = 'Talaba akademik ma`lumotlari';
		
		$this->createTable('e_student_meta', [
            'id' => $this->primaryKey(),
			'student_id_number' => $this->string(14),
			'_student'=>$this->integer()->notNull(),
			'_department'=>$this->integer(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64),
            '_specialty'=>$this->string(64)->notNull(),
            '_curriculum'=>$this->integer(),
            '_semestr'=>$this->string(64),
            '_level'=>$this->string(64),
            '_group'=>$this->integer(),
            '_subgroup'=>$this->integer(),
			'_education_year'=>$this->string(64)->notNull(),
			'_payment_form'=>$this->string(64)->notNull(),
			'_student_status'=>$this->string(64)->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_department_student_meta_fkey',
            'e_student_meta',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_type_student_meta_fkey',
            'e_student_meta',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_form_student_meta_fkey',
            'e_student_meta',
            '_education_form',
            'h_education_form',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_student_meta_fkey',
            'e_student_meta',
            '_specialty',
            'e_specialty',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_student_meta_fkey',
            'e_student_meta',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_course_student_meta_fkey',
            'e_student_meta',
            '_level',
            'h_course',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_group_student_meta_fkey',
            'e_student_meta',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_education_year_student_meta_fkey',
            'e_student_meta',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_payment_form_student_meta_fkey',
            'e_student_meta',
            '_payment_form',
            'h_payment_form',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_student_status_student_meta_fkey',
            'e_student_meta',
            '_student_status',
            'h_student_status',
            'code',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_student_meta', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student_meta');
    }
}
