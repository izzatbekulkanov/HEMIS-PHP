<?php

use yii\db\Migration;

/**
 * Class m211025_045447_alter_table_e_employee_academic_degree
 */
class m211025_045447_alter_table_e_employee_academic_degree extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('e_employee_academic_degree', 'diploma_number', $this->string(40));
        $this->alterColumn('e_employee_academic_degree', 'council_number', $this->string(40));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
