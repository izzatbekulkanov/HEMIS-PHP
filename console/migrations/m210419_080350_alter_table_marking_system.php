<?php

use yii\db\Migration;

/**
 * Class m210419_080350_alter_table_marking_system
 */
class m210419_080350_alter_table_marking_system extends Migration
{
    public function safeUp()
    {
        $this->addColumn('h_marking_system', 'gpa_limit', $this->decimal(4, 1)->defaultValue(2.4));
    }

    public function safeDown()
    {
        $this->dropColumn('h_marking_system', 'gpa_limit');
    }
}
