<?php

use yii\db\Migration;

/**
 * Class m210916_135050_create_table_e_counter
 */
class m210916_135050_create_table_e_counter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('e_counter', [
            'identifier' => $this->string()->unique(),
            'value' => $this->integer(12)->defaultValue(0),
        ]);
    }


    public function safeDown()
    {
        $this->dropTable('e_counter');
    }
}
