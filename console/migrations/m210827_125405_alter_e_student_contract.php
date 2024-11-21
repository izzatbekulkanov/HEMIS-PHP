<?php

use yii\db\Migration;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;

/**
 * Class m210827_125405_alter_e_student_contract
 */
class m210827_125405_alter_e_student_contract extends Migration
{
    public function safeUp()
    {
        $this->addColumn('e_student_contract', 'different', $this->money());
        $this->addColumn('e_student_contract', 'different_status', $this->string(64));
        $this->addColumn('e_student_contract', 'accepted', $this->boolean()->defaultValue(false));
        EStudentContract::updateAll(
            [
            'accepted' => true,
            ],
            [
                'in', 'contract_status',
                [
                    EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED,
                ]
            ]
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_student_contract', 'different');
        $this->dropColumn('e_student_contract', 'different_status');
        $this->dropColumn('e_student_contract', 'accepted');
    }

}
