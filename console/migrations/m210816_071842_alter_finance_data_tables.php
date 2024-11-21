<?php

use yii\db\Migration;

/**
 * Class m210816_071842_alter_finance_data_tables
 */
class m210816_071842_alter_finance_data_tables extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student', 'uzasbo_id_number', $this->string(30)->unique());
        $this->addColumn('e_student_contract', 'discount', $this->decimal(5, 1));
        $this->addColumn('e_paid_contract_fee', 'payment_comment',  $this->string(255));
    }

    public function safeDown()
    {
        $this->dropColumn('e_student', 'uzasbo_id_number');
        $this->dropColumn('e_student_contract', 'discount');
        $this->dropColumn('e_paid_contract_fee', 'payment_comment');
    }

}
