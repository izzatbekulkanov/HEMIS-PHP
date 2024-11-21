<?php

use yii\db\Migration;

/**
 * Class m210326_164039_alter_table_university
 */
class m210326_164039_alter_table_university extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_university', 'mailing_address', $this->text()->null());
        $this->addColumn('e_university', 'bank_details', $this->text()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_university', 'mailing_address');
        $this->dropColumn('e_university', 'bank_details');
    }
}
