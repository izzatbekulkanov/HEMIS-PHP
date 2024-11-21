<?php

use yii\db\Migration;

/**
 * Class m201217_072410_migrate_education_year
 */
class m201217_072410_migrate_education_year extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('e_education_year', true) === null) {
            $this->renameTable('h_education_year', 'e_education_year');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('h_education_year', true) === null) {
            $this->renameTable('e_education_year', 'h_education_year');
        }
    }
}
