<?php

use common\components\db\Schema;
use yii\db\Migration;

/**
 * Class m210106_165214_create_exam_tables
 */
class m210106_165214_create_exam_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talaba imtixonlari';

        $this->createTable('e_exam', [
            'id' => $this->primaryKey(),
            '_education_year' => $this->string(64)->notNull(),
            '_semester' => $this->string(64)->null(),
            '_curriculum' => $this->integer()->null(),
            '_exam_schedule' => $this->integer()->null(),
            '_subject' => $this->integer()->null(),
            '_employee' => $this->integer(),
            '_exam_type' => $this->string(64)->notNull(),
            'name' => $this->string(512)->notNull(),
            'comment' => $this->text()->null(),
            'start_at' => $this->dateTime()->notNull(),
            'question_count' => $this->integer(4)->defaultValue(50),
            'duration' => $this->integer(4)->defaultValue(60),
            'max_ball' => $this->integer(3)->defaultValue(100),
            'attempts' => $this->integer(2)->defaultValue(1),
            'random' => $this->boolean()->defaultValue(false),
            'active' => $this->boolean()->defaultValue(false),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_exam_curriculum',
            'e_exam',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_exam_exam_schedule',
            'e_exam',
            '_exam_schedule',
            'e_subject_exam_schedule',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_education_year',
            'e_exam',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_subject',
            'e_exam',
            '_subject',
            'e_subject',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_employee',
            'e_exam',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_exam_type',
            'e_exam',
            '_exam_type',
            'h_exam_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_exam', $description);

        $this->createTable('e_exam_group', [
            '_exam' => $this->integer()->null(),
            '_group' => $this->integer()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_exam_group_exam',
            'e_exam_group',
            '_exam',
            'e_exam',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_group_group',
            'e_exam_group',
            '_group',
            'e_group',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('e_exam_question', [
            'id' => $this->primaryKey(),
            '_exam' => $this->integer()->null(),
            'name' => $this->text()->notNull(),
            'content' => $this->text()->notNull(),
            'content_r' => $this->text()->notNull(),
            'answers' => 'jsonb',
            '_answer' => 'jsonb',
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_exam_question_exam',
            'e_exam_question',
            '_exam',
            'e_exam',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('e_exam_student', [
            'id' => $this->primaryKey(),
            '_exam' => $this->integer()->null(),
            '_student' => $this->integer()->null(),
            '_group' => $this->integer()->null(),
            'time' => $this->integer(5),
            'attempts' => $this->integer(3),
            'mark' => $this->decimal(5, 1),
            'correct' => $this->decimal(5, 1),
            'percent' => $this->decimal(4, 1),
            'data' => $this->json(),
            'started_at' => $this->dateTime()->null(),
            'finished_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_exam_student_exam',
            'e_exam_student',
            '_exam',
            'e_exam',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_student_student',
            'e_exam_student',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_exam_student_group',
            'e_exam_student',
            '_group',
            'e_group',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('e_exam_student');
        $this->dropTable('e_exam_question');
        $this->dropTable('e_exam_group');
        $this->dropTable('e_exam');
    }

    public function json($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(" " . Schema::TYPE_JSON, $length);
    }
}
