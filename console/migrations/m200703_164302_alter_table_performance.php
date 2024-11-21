<?php

use yii\db\Migration;

/**
 * Class m200703_164302_alter_table_performance
 */
class m200703_164302_alter_table_performance extends Migration
{
    public function safeUp()
    {
       $this->addColumn('e_performance', '_final_exam_type', $this->string(64)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('e_performance', '_final_exam_type');
    }
}
