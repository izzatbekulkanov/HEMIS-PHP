<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_subject_resource}}`.
 */
class m200822_054210_create_e_subject_resource_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'O`qituvchilarning fan bo`yicha resurslari';
		
        $this->createTable('e_subject_resource', [
            'id' => $this->primaryKey(),
			'name' => $this->string(256)->notNull(),
			'comment' => $this->text()->notNull(),
            '_curriculum'=>$this->integer()->notNull(),
            '_subject'=>$this->integer()->notNull(),
            '_language'=>$this->string(64)->notNull(),
			'_training_type'=>$this->string(64)->notNull(),
			'_subject_topic'=>$this->integer()->notNull(),
			'_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
	        '_employee'=>$this->integer()->notNull(),
			'filename' => 'jsonb',
			'path' => $this->string(500),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'published_at' => $this->dateTime(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_curriculum_e_subject_resource_fkey',
            'e_subject_resource',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_subject_resource_fkey',
            'e_subject_resource',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_language_e_subject_resource_fkey',
            'e_subject_resource',
            '_language',
            'h_language',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_training_type_e_subject_resource_fkey',
            'e_subject_resource',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_curriculum_subject_topic_e_subject_resource_fkey',
            'e_subject_resource',
            '_subject_topic',
            'e_curriculum_subject_topic',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_e_subject_resource_fkey',
            'e_subject_resource',
            '_education_year',
            'h_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_employee_e_subject_resource_fkey',
            'e_subject_resource',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
			
        $this->addCommentOnTable('e_subject_resource', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_subject_resource');
    }
	
}
