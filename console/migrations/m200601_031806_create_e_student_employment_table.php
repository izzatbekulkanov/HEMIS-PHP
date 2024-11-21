<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_employment}}`.
 */
class m200601_031806_create_e_student_employment_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        
		if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Bitiruvchi talabalarning ishga joylashish ma`lumotlari';
        
		$this->createTable('e_student_employment', [
            'id' => $this->primaryKey(),
            '_student'=>$this->integer()->notNull(),
			'student' => $this->string(256)->notNull(),	
			'student_id_number'=>$this->string(20),
			'employment_doc_number'=>$this->string(20)->notNull(),
            'employment_doc_date'=>$this->date()->notNull(),
            'company_name'=>$this->string(256)->notNull(),
            'start_date'=>$this->date()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        
		$this->addForeignKey(
            'fk_e_student_e_student_employment_fkey',
            'e_student_employment',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
		
        $this->addCommentOnTable('e_attendance', $description);
    }

   
    public function safeDown()
    {
        $this->dropTable('{{%e_student_employment}}');
    }
}
