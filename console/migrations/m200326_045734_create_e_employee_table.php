<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_employee}}`.
 */
class m200326_045734_create_e_employee_table extends Migration
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
		$description = 'Xodim shaxsiy ma`lumotlari';
		
		$this->createTable('e_employee', [
            'id' => $this->primaryKey(),
			'employee_id_number'=>$this->string(14),
			'first_name' => $this->string(100)->notNull(),
			'second_name' => $this->string(100)->notNull(),
			'third_name' => $this->string(100),
			'birth_date' => $this->date()->notNull(),
			'_gender'=>$this->string(64)->notNull(),
			'passport_number'=>$this->string(10)->notNull(),
			'passport_pin'=>$this->string(15)->notNull(),
			'_academic_degree'=>$this->string(64)->notNull(),
			'_academic_rank'=>$this->string(64)->notNull(),
			'specialty'=>$this->string(255),
	        'image' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_h_gender_employee_fkey',
            'e_employee',
            '_gender',
            'h_gender',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_academic_degree_employee_fkey',
            'e_employee',
            '_academic_degree',
            'h_academic_degree',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_academic_rank_employee_fkey',
            'e_employee',
            '_academic_rank',
            'h_academic_rank',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_employee', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_employee');
    }
}
