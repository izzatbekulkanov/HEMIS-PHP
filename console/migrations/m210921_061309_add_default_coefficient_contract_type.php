<?php

use yii\db\Migration;
use common\models\system\SystemClassifier;
use common\models\finance\EContractType;

/**
 * Class m210921_061309_add_default_coefficient_contract_type
 */
class m210921_061309_add_default_coefficient_contract_type extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);

        $contract_type = '[
          {
            "_contract_type": "18",
            "coef": "2.0"
          }
        ]';
        $contract_type = json_decode($contract_type, true);

        foreach ($contract_type as $item) {
            $model = new EContractType($item);
            if ($model->save(false)) {
                echo "Contract Type {$item['coef']} created\n";
            }
        }

    }

    public function safeDown()
    {

    }
}
