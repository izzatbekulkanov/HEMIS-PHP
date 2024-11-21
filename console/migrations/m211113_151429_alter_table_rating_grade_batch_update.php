<?php

use yii\db\Migration;
use common\models\curriculum\RatingGrade;

/**
 * Class m211113_151429_alter_table_rating_grade_batch_update
 */
class m211113_151429_alter_table_rating_grade_batch_update extends Migration
{
    public function safeUp()
    {
        $rating_grade = '[
          {
            "code": "16",
            "name": "Fan qaydnomasi (yakuniy)",
            "template": "subject_final"
          }
        ]';
        $rating_grade = json_decode($rating_grade, true);

        foreach ($rating_grade as $item) {
            $model = RatingGrade::findOne(['code' => RatingGrade::RATING_GRADE_SUBJECT_FINAL]);
            if ($model === null)
                $model = new RatingGrade($item);
            if ($model->save(false)) {
                echo "Rating Grade {$item['name']} created\n";
            }
        }

        if ($type = RatingGrade::findOne(['code' => RatingGrade::RATING_GRADE_SUBJECT])) {
            $type->setTranslation('name', 'Fan qaydnomasi (to‘liq)', \common\components\Config::LANGUAGE_UZBEK);
            $type->save();
        }
    }

    public function safeDown()
    {

    }
}
