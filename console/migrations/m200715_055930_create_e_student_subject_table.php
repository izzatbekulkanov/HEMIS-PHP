<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_subject}}`.
 */
class m200715_055930_create_e_student_subject_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning semestrda o`qiydigan fanlari';
		
        $this->createTable('e_student_subject', [
            'id' => $this->primaryKey(),
            '_curriculum'=>$this->integer()->notNull(),
            '_subject'=>$this->integer()->notNull(),
	        '_student'=>$this->integer()->notNull(),
			'_group'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_curriculum_e_student_subject_fkey',
            'e_student_subject',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_subject_e_student_subject_fkey',
            'e_student_subject',
            '_subject',
            'e_subject',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_student_e_student_subject_fkey',
            'e_student_subject',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_group_e_student_subject_fkey',
            'e_student_subject',
            '_group',
            'e_group',
            'id',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_year_e_student_subject_fkey',
            'e_student_subject',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
        $this->createIndex('student_subject_student_uniq',
            'e_student_subject',
            ['_curriculum', '_student','_education_year', '_semester','_subject'],
            true);
			
        $this->addCommentOnTable('e_student_subject', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_subject');
    }
	
}
