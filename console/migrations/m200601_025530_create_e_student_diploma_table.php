<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_diploma}}`.
 */
class m200601_025530_create_e_student_diploma_table extends Migration
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
        $description = 'Bitiruvchi talabalarning diplomlari';
		
        $this->createTable('e_student_diploma', [
            'id' => $this->primaryKey(),
            '_specialty'=>$this->string(64)->notNull(),
			'_student'=>$this->integer()->notNull(),
			'specialty' => $this->string(256)->notNull(),	
			'student' => $this->string(256)->notNull(),	
			'student_id_number'=>$this->string(20), 
			'diploma_number'=>$this->string(20)->notNull(),
            'register_number'=>$this->string(30)->notNull(),
            'register_date'=>$this->date()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_student_e_student_diploma_fkey',
            'e_student_diploma',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_specialty_e_student_diploma_fkey',
            'e_student_diploma',
            '_specialty',
            'e_specialty',
            'code',
            'CASCADE'
        );
		
        $this->addCommentOnTable('e_student_diploma', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student_diploma');
    }
}
