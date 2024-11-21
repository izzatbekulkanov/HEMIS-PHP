<?php

use common\models\finance\EMinimumWage;
use common\models\finance\EStipendValue;
use common\models\finance\EContractType;
use common\models\finance\EPaidContractFee;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use common\models\finance\EContractPrice;
use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m210430_061212_batch_finance_const_table
 */
class m210430_061212_batch_finance_const_table extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);
        EPaidContractFee::deleteAll();
        EStudentContract::deleteAll();
        EStudentContractType::deleteAll();
        EContractPrice::deleteAll();

        EMinimumWage::deleteAll();
        EStipendValue::deleteAll();
        EContractType::deleteAll();

        //$this->truncateTable(EContractType::tableName());

        $min_wage = '[
          {
            "name": "747300.0",
            "begin_date": "2021-02-01",
            "document": "Президент қарори № ПҚ-4938, 30.12.2020",
            "current_status": 1
          },
          {
            "name": "679330.0",
            "begin_date": "2020-09-01",
            "document": "Президент фармони № ПФ-6038, 30.07.2020",
            "current_status": 0
          },
          {
            "name": "634880.0",
            "begin_date": "2019-09-01",
            "document": "Президент фармони № ПФ-5723, 21.05.2019",
            "current_status": 0
          }
        ]';
        $min_wage = json_decode($min_wage, true);

        foreach ($min_wage as $item) {
            $model = new EMinimumWage($item);
            if ($model->save(false)) {
                echo "Minimum Wage {$item['name']} created\n";
            }
        }


        $scholarship_value = '[
          {
            "_stipend_rate": "11",
            "stipend_value": "470800.0",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "12",
            "stipend_value": "1992310.1",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "13",
            "stipend_value": "1328207.1",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "14",
            "stipend_value": "564960.0",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "15",
            "stipend_value": "564960.0",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "16",
            "stipend_value": "94160.0",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "17",
            "stipend_value": "706200.0",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "18",
            "stipend_value": "470800.0",
            "begin_date": "2021-02-01"
          },
          {
            "_stipend_rate": "19",
            "stipend_value": "706200.0",
            "begin_date": "2021-02-01"
          }
        ]';
        $scholarship_value = json_decode($scholarship_value, true);

        foreach ($scholarship_value as $item) {
            $model = new EStipendValue($item);
            if ($model->save(false)) {
                echo "Stipend Value {$item['stipend_value']} created\n";
            }
        }


        $contract_type = '[
          {
            "_contract_type": "11",
            "coef": "1.0"
          },
          {
            "_contract_type": "12",
            "coef": "1.5"
          },
          {
            "_contract_type": "13",
            "coef": "2.0"
          },
          {
             "_contract_type": "14",
            "coef": "2.5"
          },
          {
             "_contract_type": "15",
            "coef": "3.0"
          },
          {
             "_contract_type": "16",
            "coef": "1.0"
          },
          {
             "_contract_type": "17",
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
