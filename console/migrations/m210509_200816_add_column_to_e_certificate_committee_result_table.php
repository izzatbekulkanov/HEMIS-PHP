<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_certificate_committee_result}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%e_subject}}`
 */
class m210509_200816_add_column_to_e_certificate_committee_result_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%e_certificate_committee_result}}', '_subject', $this->integer());

        // creates index for column `_subject`
        $this->createIndex(
            '{{%idx-e_certificate_committee_result-_subject}}',
            '{{%e_certificate_committee_result}}',
            '_subject'
        );

        // add foreign key for table `{{%e_subject}}`
        $this->addForeignKey(
            '{{%fk-e_certificate_committee_result-_subject}}',
            '{{%e_certificate_committee_result}}',
            '_subject',
            '{{%e_subject}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%e_subject}}`
        $this->dropForeignKey(
            '{{%fk-e_certificate_committee_result-_subject}}',
            '{{%e_certificate_committee_result}}'
        );

        // drops index for column `_subject`
        $this->dropIndex(
            '{{%idx-e_certificate_committee_result-_subject}}',
            '{{%e_certificate_committee_result}}'
        );

        $this->dropColumn('{{%e_certificate_committee_result}}', '_subject');
    }
}
