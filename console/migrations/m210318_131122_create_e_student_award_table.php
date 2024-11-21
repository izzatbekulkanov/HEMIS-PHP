<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_award}}`.
 */
class m210318_131122_create_e_student_award_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $description = 'Student yutuqlari';
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%e_student_award}}', [
            'id' => $this->primaryKey(),
            '_student' => $this->integer()->notNull(),
            '_curriculum' => $this->integer()->notNull(),
            '_student_level' => $this->string(64)->notNull(),
            '_student_group' => $this->integer()->notNull(),
            '_award_group' => $this->string(64)->notNull(),
            '_award_category' => $this->string(64)->notNull(),
            'award_document' => $this->string(1024)->notNull(),
            'award_year' => $this->integer(4)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'active' => $this->boolean()->defaultValue(true),
        ], $tableOptions);

        $this->addCommentOnTable('e_student_award', $description);

        $this->addForeignKey(
            'fk_e_student_e_student_award_fkey',
            'e_student_award',
            '_student',
            'e_student',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_student_award_fkey',
            'e_student_award',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_course_e_student_award_fkey',
            'e_student_award',
            '_student_level',
            'h_course',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_student_award_fkey',
            'e_student_award',
            '_student_group',
            'e_group',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_student_success_e_student_award_fkey',
            'e_student_award',
            '_award_category',
            'h_student_success',
            'code',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_student_success_e_student_award_group_fkey',
            'e_student_award',
            '_award_group',
            'h_student_success',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_student_award}}');
    }
}
