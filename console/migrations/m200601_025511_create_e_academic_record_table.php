<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_academic_record}}`.
 */
class m200601_025511_create_e_academic_record_table extends Migration
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
        $description = 'Talabaning akademik yozuvlari ma`lumotlari';
		
        $this->createTable('e_academic_record', [
            'id' => $this->primaryKey(),
            '_curriculum'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_student'=>$this->integer()->notNull(),
			'_subject'=>$this->integer()->notNull(),
			'curriculum' => $this->string(256)->notNull(),
			'education_year' => $this->string(256)->notNull(),
			'semester' => $this->string(256)->notNull(),
			'student' => $this->string(256)->notNull(),	
			'student_id_number'=>$this->string(20),
			'subject' => $this->string(256)->notNull(),
			'total_acload'=>$this->integer(),
			'credit'=>$this->integer(),
			'total_point'=>$this->integer()->notNull(),
            'grade'=>$this->integer()->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_curriculum_e_academic_record_fkey',
            'e_academic_record',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_education_year_e_academic_record_fkey',
            'e_academic_record',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_student_e_academic_record_fkey',
            'e_academic_record',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_e_subject_e_academic_record_fkey',
            'e_academic_record',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		
        $this->addCommentOnTable('e_academic_record', $description);
		
    }

    public function safeDown()
    {
        $this->dropTable('e_academic_record');
    }
}
