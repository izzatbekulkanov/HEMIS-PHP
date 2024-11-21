<?php

use yii\db\Migration;

/**
 * Class m210728_124509_create_table_student_ptt
 */
class m210728_124509_create_table_student_ptt extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning shaxsiy jadvali';

        $this->createTable('e_student_ptt', [
            'id' => $this->primaryKey(),
            '_student' => $this->integer()->notNull(),
            '_department' => $this->integer()->notNull(),
            '_curriculum' => $this->integer()->notNull(),
            '_specialty' => $this->integer()->notNull(),
            '_group' => $this->integer()->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            '_education_form' => $this->string(64)->notNull(),

            '_education_year' => $this->string(64)->notNull(),
            '_semester' => $this->integer()->notNull(),
            '_decree' => $this->integer()->notNull(),
            'subjects_count' => $this->integer(),
            'graded_count' => $this->integer(),

            'number' => $this->string(64)->notNull()->unique(),
            'date' => $this->dateTime()->notNull(),
            'data' => 'jsonb',

            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_student_ptt_student',
            'e_student_ptt',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );


        $this->addForeignKey(
            'fk_e_student_ptt_department',
            'e_student_ptt',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_specialty',
            'e_student_ptt',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_curriculum',
            'e_student_ptt',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_ptt_semester',
            'e_student_ptt',
            '_semester',
            'h_semestr',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_group',
            'e_student_ptt',
            '_group',
            'e_group',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_education_year',
            'e_student_ptt',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_education_form',
            'e_student_ptt',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_education_type',
            'e_student_ptt',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_decree',
            'e_student_ptt',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->createIndex('idx_e_student_ptt_student_semester', 'e_student_ptt', ['_student', '_semester'], true);

        $this->addCommentOnTable('e_student_ptt', $description);


        $this->createTable('e_student_ptt_subject', [
            'id' => $this->primaryKey(),
            '_student_ptt' => $this->integer()->notNull(),
            '_curriculum_subject' => $this->integer()->notNull(),
            'total_acload' => $this->integer(),
            'credit' => $this->decimal(5, 2),
            'total_point' => $this->decimal(5, 2),
            'grade' => $this->decimal(5, 2),

            'updated_at' => $this->dateTime()->notNull()->defaultValue('NOW()'),
            'created_at' => $this->dateTime()->notNull()->defaultValue('NOW()'),
        ], $tableOptions);


        $this->addForeignKey(
            'fk_e_student_ptt_subject_student_ptt',
            'e_student_ptt_subject',
            '_student_ptt',
            'e_student_ptt',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_ptt_subject_curriculum_subject',
            'e_student_ptt_subject',
            '_curriculum_subject',
            'e_curriculum_subject',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_student_ptt_student_ptt_curriculum_subject_unique', 'e_student_ptt_subject', ['_student_ptt', '_curriculum_subject'], true);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_ptt_subject');
        $this->dropTable('e_student_ptt');
    }
}
