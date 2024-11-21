<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_academic_information_data_subject}}`.
 */
class m210807_202522_create_e_academic_information_data_subject_table extends Migration
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
        $description = 'Akademik ma`lumotnoma fanlari ma`lumotlari';

        $this->createTable('e_academic_information_data_subject', [
            'id' => $this->primaryKey(),
            '_academic_information_data'=>$this->integer()->notNull(),
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
            'idx-e_academic_information_data_subject-_academic_information_data',
            'e_academic_information_data_subject',
            '_academic_information_data'
        );
        $this->createIndex(
            'idx-e_academic_information_data_subject-_student',
            'e_academic_information_data_subject',
            '_student'
        );
        $this->createIndex(
            'idx-e_academic_information_data_subject-_curriculum',
            'e_academic_information_data_subject',
            '_curriculum'
        );
        $this->createIndex(
            'idx-e_academic_information_data_subject-_education_year',
            'e_academic_information_data_subject',
            '_education_year'
        );
        $this->createIndex(
            'idx-e_academic_information_data_subject-_subject',
            'e_academic_information_data_subject',
            '_subject'
        );
        $this->addForeignKey(
            'fk_e__academic_information_data_e_academic_information_data_subject_fkey',
            'e_academic_information_data_subject',
            '_academic_information_data',
            'e_academic_information_data',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_student_e_academic_information_data_subject_fkey',
            'e_academic_information_data_subject',
            '_student',
            'e_student',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_academic_information_data_subject_fkey',
            'e_academic_information_data_subject',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_academic_information_data_subject_fkey',
            'e_academic_information_data_subject',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT'
        );
        $this->addForeignKey(
            'fk_e_subject_e_academic_information_data_subject_fkey',
            'e_academic_information_data_subject',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT'
        );
        $this->addCommentOnTable('e_academic_information_data_subject', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'idx-e_academic_information_data_subject-_academic_information_data',
            'e_academic_information_data_subject'
        );
        $this->dropForeignKey(
            'idx-e_academic_information_data_subject-_student',
            'e_academic_information_data_subject'
        );
        $this->dropForeignKey(
            'idx-e_academic_information_data_subject-_curriculum',
            'e_academic_information_data_subject'
        );
        $this->dropForeignKey(
            'idx-e_academic_information_data_subject-_education_year',
            'e_academic_information_data_subject'
        );
        $this->dropForeignKey(
            'idx-e_academic_information_data_subject-_subject',
            'e_academic_information_data_subject'
        );
        $this->dropTable('e_academic_information_data_subject');
    }
}
