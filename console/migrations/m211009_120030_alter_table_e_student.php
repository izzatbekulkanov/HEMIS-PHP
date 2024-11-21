<?php

use yii\db\Migration;

/**
 * Class m211009_120030_alter_table_e_student
 */
class m211009_120030_alter_table_e_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_student', 'password_valid', $this->boolean()->defaultValue(true));
        $this->addColumn('e_student', 'password_date', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_student', 'password_valid');
        $this->dropColumn('e_student', 'password_date');
    }
}
