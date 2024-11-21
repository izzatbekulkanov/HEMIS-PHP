<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_project}}`.
 */
class m201129_042102_create_e_project_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Muassasadagi loyihalar to`g`risida ma`lumot';
		
        $this->createTable('e_project', [
            'id' => $this->primaryKey(),
			'name' => $this->string(255)->notNull(),
			'project_number' => $this->string(255)->notNull(),
			'_department'=>$this->integer()->notNull(),
            '_project_type'=>$this->string(64)->notNull(),
            '_locality'=>$this->string(64)->notNull(),
            '_project_currency'=>$this->string(64)->notNull(),
			'contract_number' => $this->string(255)->notNull(),
			'contract_date' => $this->date()->notNull(),
			'start_date'=>$this->date(),
			'end_date'=>$this->date(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_department_e_project_fkey',
            'e_project',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_project_type_e_project_fkey',
            'e_project',
            '_project_type',
            'h_project_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_locality_e_project_fkey',
            'e_project',
            '_locality',
            'h_locality',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_project_currency_e_project_fkey',
            'e_project',
            '_project_currency',
            'h_project_currency',
            'code',
            'RESTRICT',
            'CASCADE'
        );
			
        $this->addCommentOnTable('e_project', $description);
    }

	public function safeDown()
    {
        $this->dropTable('e_project');
    }
}
