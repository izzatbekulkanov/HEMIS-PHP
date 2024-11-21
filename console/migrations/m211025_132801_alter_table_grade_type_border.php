<?php

use yii\db\Migration;

/**
 * Class m211025_132801_alter_table_grade_type_border
 */
class m211025_132801_alter_table_grade_type_border extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('h_grade_type', 'min_border', $this->decimal(10, 2));
        $this->alterColumn('h_grade_type', 'max_border', $this->decimal(10, 2));
    }

    public function safeDown()
    {
        $this->alterColumn('h_grade_type', 'min_border', $this->decimal(10, 1));
        $this->alterColumn('h_grade_type', 'max_border', $this->decimal(10, 1));
    }
}
