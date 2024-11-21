<?php

use yii\db\Migration;

/**
 * Class m200711_042927_alter_table_performance
 */
class m200711_042927_alter_table_performance extends Migration
{
    public function safeUp()
    {
       $this->addColumn('e_performance', 'send_record_status', $this->integer(3)->defaultValue(0));
       $this->addColumn('e_performance', 'send_record_date', $this->dateTime());
	}

    public function safeDown()
    {
        $this->dropColumn('e_performance', 'send_record_status');
        $this->dropColumn('e_performance', 'send_record_date');
    }
}
