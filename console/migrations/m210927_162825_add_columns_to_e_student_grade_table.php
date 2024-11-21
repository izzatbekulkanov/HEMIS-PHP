<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_student_grade}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_curriculum_subject_topic}}`
 */
class m210927_162825_add_columns_to_e_student_grade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_student_grade}}', '_subject_topic', $this->integer());

        // creates index for column `_subject_topic`
        $this->createIndex(
            '{{%idx-e_student_grade-_subject_topic}}',
            '{{%e_student_grade}}',
            '_subject_topic'
        );

        // add foreign key for table `{{%e_curriculum_subject_topic}}`
        $this->addForeignKey(
            '{{%fk-e_student_grade-_subject_topic}}',
            '{{%e_student_grade}}',
            '_subject_topic',
            '{{%e_curriculum_subject_topic}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_curriculum_subject_topic}}`
        $this->dropForeignKey(
            '{{%fk-e_student_grade-_subject_topic}}',
            '{{%e_student_grade}}'
        );

        // drops index for column `_subject_topic`
        $this->dropIndex(
            '{{%idx-e_student_grade-_subject_topic}}',
            '{{%e_student_grade}}'
        );

        $this->dropColumn('{{%e_student_grade}}', '_subject_topic');
    }
}
