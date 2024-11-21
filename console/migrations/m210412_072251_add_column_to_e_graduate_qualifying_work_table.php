<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_graduate_qualifying_work}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_group}}`
 */
class m210412_072251_add_column_to_e_graduate_qualifying_work_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_graduate_qualifying_work}}', '_group', $this->integer());

        // creates index for column `_group`
        $this->createIndex(
            '{{%idx-e_graduate_qualifying_work-_group}}',
            '{{%e_graduate_qualifying_work}}',
            '_group'
        );

        // add foreign key for table `{{%e_group}}`
        $this->addForeignKey(
            '{{%fk-e_graduate_qualifying_work-_group}}',
            '{{%e_graduate_qualifying_work}}',
            '_group',
            '{{%e_group}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_group}}`
        $this->dropForeignKey(
            '{{%fk-e_graduate_qualifying_work-_group}}',
            '{{%e_graduate_qualifying_work}}'
        );

        // drops index for column `_group`
        $this->dropIndex(
            '{{%idx-e_graduate_qualifying_work-_group}}',
            '{{%e_graduate_qualifying_work}}'
        );

        $this->dropColumn('{{%e_graduate_qualifying_work}}', '_group');
    }
}
