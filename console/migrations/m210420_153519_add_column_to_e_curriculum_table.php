<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_curriculum}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_qualification}}`
 */
class m210420_153519_add_column_to_e_curriculum_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_curriculum}}', '_qualification', $this->integer());

        // creates index for column `_qualification`
        $this->createIndex(
            '{{%idx-e_curriculum-_qualification}}',
            '{{%e_curriculum}}',
            '_qualification'
        );

        // add foreign key for table `{{%e_qualification}}`
        $this->addForeignKey(
            '{{%fk-e_curriculum-_qualification}}',
            '{{%e_curriculum}}',
            '_qualification',
            '{{%e_qualification}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_qualification}}`
        $this->dropForeignKey(
            '{{%fk-e_curriculum-_qualification}}',
            '{{%e_curriculum}}'
        );

        // drops index for column `_qualification`
        $this->dropIndex(
            '{{%idx-e_curriculum-_qualification}}',
            '{{%e_curriculum}}'
        );

        $this->dropColumn('{{%e_curriculum}}', '_qualification');
    }
}
