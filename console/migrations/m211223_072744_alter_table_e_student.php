<?php

use yii\db\Migration;

/**
 * Class m211223_072744_alter_table_e_student
 */
class m211223_072744_alter_table_e_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('e_student', 'geo_location', $this->string(2000));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
