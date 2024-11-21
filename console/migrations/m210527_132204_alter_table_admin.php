<?php

use yii\db\Migration;

/**
 * Class m210527_132204_alter_table_admin
 */
class m210527_132204_alter_table_admin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_admin', 'password_valid', $this->boolean()->defaultValue(true));
        $this->addColumn('e_admin', 'password_date', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_admin', 'password_valid');
        $this->dropColumn('e_admin', 'password_date');
    }
}
