<?php

use yii\db\Migration;
use common\models\system\SystemClassifier;
use common\models\finance\EContractType;
use common\models\system\classifier\ContractType;

/**
 * Class m211004_165712_reindex_classifiers_tables
 */
class m211004_165712_add_default_coefficient_foreign_contract_type extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);
        $contract_type = '[
          {
            "_contract_type": "19",
            "coef": "1.0"
          }
        ]';
        $contract_type = json_decode($contract_type, true);

        foreach ($contract_type as $item) {
            $model = EContractType::findOne(['_contract_type'=>ContractType::CONTRACT_TYPE_FOREIGN]);
            if($model === null)
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
