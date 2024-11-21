<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_graduate_qualifying_work}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_department}}`
 */
class m210415_042341_add_column_to_e_graduate_qualifying_work_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_graduate_qualifying_work}}', '_department', $this->integer());

        // creates index for column `_department`
        $this->createIndex(
            '{{%idx-e_graduate_qualifying_work-_department}}',
            '{{%e_graduate_qualifying_work}}',
            '_department'
        );

        // add foreign key for table `{{%e_department}}`
        $this->addForeignKey(
            '{{%fk-e_graduate_qualifying_work-_department}}',
            '{{%e_graduate_qualifying_work}}',
            '_department',
            '{{%e_department}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_department}}`
        $this->dropForeignKey(
            '{{%fk-e_graduate_qualifying_work-_department}}',
            '{{%e_graduate_qualifying_work}}'
        );

        // drops index for column `_department`
        $this->dropIndex(
            '{{%idx-e_graduate_qualifying_work-_department}}',
            '{{%e_graduate_qualifying_work}}'
        );

        $this->dropColumn('{{%e_graduate_qualifying_work}}', '_department');
    }
}
