<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_project_executor}}`.
 */
class m201129_042156_create_e_project_executor_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Loyihalarning ijrochilari to`g`risidagi ma`lumot';
		
        $this->createTable('e_project_executor', [
            'id' => $this->primaryKey(),
			'_project'=>$this->integer()->notNull(),
            '_project_executor_type'=>$this->string(64)->notNull(),
            '_executor_type'=>$this->integer(3)->notNull(),
			'_id_number'=>$this->integer(),
			'outsider'=>$this->string(255),
            'start_date'=>$this->date()->notNull(),
			'end_date'=>$this->date(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_project_e_project_executor_fkey',
            'e_project_executor',
            '_project',
            'e_project',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_project_executor_type_e_project_executor_fkey',
            'e_project_executor',
            '_project_executor_type',
            'h_project_executor_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );	
        $this->addCommentOnTable('e_project_executor', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_project_executor');
    }
}
