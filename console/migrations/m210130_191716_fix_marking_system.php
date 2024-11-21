<?php

use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use yii\db\Migration;

/**
 * Class m210130_191716_fix_marking_system
 */
class m210130_191716_fix_marking_system extends Migration
{

    public function safeUp()
    {
        $data = '[
  {
    "code": "13",
    "name": "Kredit baholash tizimi",
    "minimum_limit": 60,
    "count_final_exams": 2
  },
  {
    "code": "11",
    "name": "Reyting baholash tizimi",
    "minimum_limit": 55,
    "count_final_exams": 3
  },
  {
    "code": "12",
    "name": "5 baholik baholash tizimi",
    "minimum_limit": 55,
    "count_final_exams": 3
  }
]';
        $data = json_decode($data, true);

        foreach ($data as $item) {
            if ($model = MarkingSystem::findOne(['code' => $item['code']])) {
                if ($model->updateAttributes($item)) {
                    echo "Marking system {$item['name']} updated\n";
                }
            } else {
                $model = new MarkingSystem($item);
                if ($model->save(false)) {
                    echo "Marking system {$item['name']} created\n";
                }
            }
        }


        $grades = '[
  {
    "code": "12",
    "name": "4",
    "_marking_system": "11",
    "min_border": 71.0,
    "max_border": 85.0
  },
  {
    "code": "13",
    "name": "3",
    "_marking_system": "11",
    "min_border": 55.0,
    "max_border": 70.0
  },
  {
    "code": "14",
    "name": "2",
    "_marking_system": "11",
    "min_border": 0.0,
    "max_border": 54.0
  },
  {
    "code": "11",
    "name": "5",
    "_marking_system": "11",
    "min_border": 86.0,
    "max_border": 100.0
  },
  {
    "code": "11",
    "name": "5",
    "_marking_system": "13",
    "min_border": 90.0,
    "max_border": 100.0
  },
  {
    "code": "12",
    "name": "4",
    "_marking_system": "13",
    "min_border": 70.0,
    "max_border": 89.0
  },
  {
    "code": "13",
    "name": "3",
    "_marking_system": "13",
    "min_border": 60.0,
    "max_border": 69.0
  },
  {
    "code": "14",
    "name": "2",
    "_marking_system": "13",
    "min_border": 0.0,
    "max_border": 59.0
  },
  {
    "code": "11",
    "name": "5",
    "_marking_system": "12",
    "min_border": 4.5,
    "max_border": 5.0
  },
  {
    "code": "12",
    "name": "4",
    "_marking_system": "12",
    "min_border": 3.5,
    "max_border": 4.4
  },
  {
    "code": "13",
    "name": "3",
    "_marking_system": "12",
    "min_border": 2.5,
    "max_border": 3.4
  },
  {
    "code": "14",
    "name": "2",
    "_marking_system": "12",
    "min_border": 0.5,
    "max_border": 2.4
  }
]';

        $grades = json_decode($grades, true);

        foreach ($grades as $item) {
            $model = GradeType::findOne([
                '_marking_system' => $item['_marking_system'],
                'code' => $item['code'],
            ]);

            if ($model) {
                if ($model->updateAttributes($item)) {
                    echo "Grade type {$item['name']} of {$item['_marking_system']} updated\n";
                }
            } else {
                $model = new GradeType($item);
                if ($model->save(false)) {
                    echo "Grade type {$item['name']} of {$item['_marking_system']} created\n";
                }
            }
        }
    }


    public function safeDown()
    {
    }

}
