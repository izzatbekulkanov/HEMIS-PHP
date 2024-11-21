<?php

use yii\db\Migration;

/**
 * Class m211104_155444_alter_table_e_student_contract_invoice
 */
class m211104_155444_alter_table_e_student_contract_invoice extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract_invoice', 'hash', $this->string(36)->unique());
        $this->createIndex('idx_student_contract_invoice_hash', 'e_student_contract_invoice', ['hash'], true);
        foreach (\common\models\finance\EStudentContractInvoice::find()->all() as $item) {
            $item->updateAttributes(['hash' => gen_uuid()]);
        }
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract_invoice', 'hash');
    }
}
