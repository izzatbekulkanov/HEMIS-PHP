<?php

use yii\db\Migration;

/**
 * Class m211003_172544_alter_table_employee_meta
 */
class m211003_172544_alter_table_employee_meta extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_employee_meta', 'decree_number', $this->string(64)->null());
        $this->addColumn('e_employee_meta', 'decree_date', $this->date()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_employee_meta', 'decree_number');
        $this->dropColumn('e_employee_meta', 'decree_date');
    }

}
