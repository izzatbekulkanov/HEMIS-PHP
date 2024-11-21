<?php

use yii\db\Migration;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;

/**
 * Class m210909_175633_alter_change_status_student_contact
 */
class m210909_175633_alter_change_status_student_contact extends Migration
{
    public function safeUp()
    {
        EStudentContract::updateAll(
            [
                'accepted' => false,
                'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_PROCESS,

            ],
            [
                'in', 'contract_status',
                [
                    EStudentContractType::CONTRACT_REQUEST_STATUS_READY,
                    EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED,
                ]
            ]
        );
    }

    public function safeDown()
    {

    }
}
