<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_transcript_subject}}`.
 */
class m210802_162310_create_e_transcript_subject_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Akademik ma`lumotnoma (transkript) ma`lumotlari';

        $this->createTable('e_transcript_subject', [
            'id' => $this->primaryKey(),
            '_academic_information'=>$this->integer()->notNull(),
            '_student'=>$this->integer()->notNull(),
            '_curriculum'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
            '_semester'=>$this->string(64)->notNull(),
            '_subject'=>$this->integer()->notNull(),
            'curriculum_name' => $this->string(256)->notNull(),
            'education_year_name' => $this->string(255)->notNull(),
            'semester_name' => $this->string(255)->notNull(),
            'student_name' => $this->string(255)->notNull(),
            'subject_name' => $this->string(255)->notNull(),
            'total_acload'=>$this->integer(),
            'credit'=>$this->decimal(5, 2),
            'total_point'=>$this->decimal(5, 2),
            'grade'=>$this->decimal(5, 2),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull()->defaultValue('NOW()'),
            'created_at' => $this->dateTime()->notNull()->defaultValue('NOW()'),
        ], $tableOptions);

        $this->createIndex(
            'idx-e_transcript_subject-_academic_information',
            'e_transcript_subject',
            '_academic_information'
        );
        $this->createIndex(
            'idx-e_transcript_subject-_student',
            'e_transcript_subject',
            '_student'
        );
        $this->createIndex(
            'idx-e_transcript_subject-_curriculum',
            'e_transcript_subject',
            '_curriculum'
        );
        $this->createIndex(
            'idx-e_transcript_subject-_education_year',
            'e_transcript_subject',
            '_education_year'
        );
        $this->createIndex(
            'idx-e_transcript_subject-_subject',
            'e_transcript_subject',
            '_subject'
        );
        $this->addForeignKey(
            'fk_e_academic_information_e_transcript_subject_fkey',
            'e_transcript_subject',
            '_academic_information',
            'e_academic_information',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_student_e_transcript_subject_fkey',
            'e_transcript_subject',
            '_student',
            'e_student',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_transcript_subject_fkey',
            'e_transcript_subject',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_transcript_subject_fkey',
            'e_transcript_subject',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_subject_e_transcript_subject_fkey',
            'e_transcript_subject',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT'
        );
        $this->addCommentOnTable('e_academic_record', $description);
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'idx-e_transcript_subject-_academic_information',
            'e_transcript_subject'
        );
        $this->dropForeignKey(
            'idx-e_transcript_subject-_student',
            'e_transcript_subject'
        );
        $this->dropForeignKey(
            'idx-e_transcript_subject-_curriculum',
            'e_transcript_subject'
        );
        $this->dropForeignKey(
            'idx-e_transcript_subject-_education_year',
            'e_transcript_subject'
        );
        $this->dropForeignKey(
            'idx-e_transcript_subject-_subject',
            'e_transcript_subject'
        );
        $this->dropTable('e_transcript_subject');
    }
}
