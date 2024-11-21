<?php

use yii\db\Migration;

/**
 * Class m211209_144121_create_table_exam_group_student
 */
class m211209_144121_create_table_exam_group_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('e_exam_exclude', [
            'id' => $this->primaryKey(),
            '_exam' => $this->integer(),
            '_student' => $this->integer(),
            'excluded' => $this->boolean()->defaultValue(true),
        ]);

        $this->createIndex('idx_exam_exclude_student_unique', 'e_exam_exclude', ['_exam', '_student'], true);

        $this->addForeignKey(
            'fk_e_exam_exclude_exam',
            'e_exam_exclude',
            '_exam',
            'e_exam',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_exam_exclude_student',
            'e_exam_exclude',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_exam_exclude');
    }
}
