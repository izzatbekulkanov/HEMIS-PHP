<?php

use yii\db\Migration;

/**
 * Class m211221_105706_alter_table_empty_student_contract_invoice
 */
class m211221_105706_alter_table_empty_student_contract_invoice extends Migration
{
    public function safeUp()
    {
        foreach (\common\models\finance\EStudentContractInvoice::find()->where(['hash'=>null])->all() as $item) {
            $item->updateAttributes(['hash' => gen_uuid()]);
        }
    }


    public function safeDown()
    {

    }

}
