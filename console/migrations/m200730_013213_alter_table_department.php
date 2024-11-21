<?php

use yii\db\Migration;

/**
 * Class m200730_013213_alter_table_department
 */
class m200730_013213_alter_table_department extends Migration
{
   public function safeUp()
   {
        $this->addColumn('e_department', '_type', $this->string(64)->defaultValue(11));

        $this->addForeignKey(
            'fk_e_department_type',
            'e_department',
            '_type',
            'h_locality_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_department', '_type');
    }
}
