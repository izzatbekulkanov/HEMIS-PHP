<?php

use yii\db\Migration;

/**
 * Class m210103_100616_alter_table_subject_task
 */
class m210103_100616_alter_table_subject_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('e_subject_resource_question', '_subject_topic', $this->integer()->null());
        $this->addColumn('e_subject_task', 'random', $this->boolean()->defaultValue(false));
        $this->addColumn('e_subject_resource_question', '_subject_task', $this->integer()->null());

        $this->addForeignKey(
            'fk_e_subject_resource_question_subject_task',
            'e_subject_resource_question',
            '_subject_task',
            'e_subject_task',
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
        $this->dropForeignKey('fk_e_subject_resource_question_subject_task', 'e_subject_resource_question');
        $this->dropColumn('e_subject_resource_question', '_subject_task');
        $this->dropColumn('e_subject_task', 'random');
    }
}
