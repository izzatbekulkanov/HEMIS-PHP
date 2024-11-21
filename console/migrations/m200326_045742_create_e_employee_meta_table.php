<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_employee_meta}}`.
 */
class m200326_045742_create_e_employee_meta_table extends Migration
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
		$description = 'Xodim ish joyi ma`lumotlari';
		
		$this->createTable('e_employee_meta', [
            'id' => $this->primaryKey(),
			'employee_id_number' => $this->string(14),
			'_employee'=>$this->integer()->notNull(),
			'_department'=>$this->integer()->notNull(),
            '_position'=>$this->string(64)->notNull(),
            '_employment_form'=>$this->string(64)->notNull(),
            '_employment_staff'=>$this->string(64)->notNull(),
            '_employee_status'=>$this->string(64)->notNull(),
            'contract_number'=>$this->string(64)->notNull(),
			'contract_date' => $this->date()->notNull(),
		    'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_department_employee_meta_fkey',
            'e_employee_meta',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_position_employee_meta_fkey',
            'e_employee_meta',
            '_position',
            'h_teacher_position_type',
            'code',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_employment_form_employee_meta_fkey',
            'e_employee_meta',
            '_employment_form',
            'h_employment_form',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_employment_staff_employee_meta_fkey',
            'e_employee_meta',
            '_employment_staff',
            'h_employment_staff',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_employee_status_employee_meta_fkey',
            'e_employee_meta',
            '_employee_status',
            'h_teacher_status',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_employee_meta');
    }
}
