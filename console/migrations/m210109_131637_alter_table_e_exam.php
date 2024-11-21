<?php

use yii\db\Migration;

/**
 * Class m210109_131637_alter_table_e_exam
 */
class m210109_131637_alter_table_e_exam extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_exam', 'finish_at', $this->dateTime()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_exam', 'finish_at');
    }

}
