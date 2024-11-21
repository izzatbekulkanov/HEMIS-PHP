<?php

use yii\db\Migration;
use common\models\curriculum\RatingGrade;
/**
 * Class m211115_075224_alter_table_rating_grade_batch_update
 */
class m211115_075224_alter_table_rating_grade_batch_update extends Migration
{
    public function safeUp()
    {
        if ($type = RatingGrade::findOne(['code' => RatingGrade::RATING_GRADE_SUBJECT])) {
            $type->setTranslation('name', 'Fan qaydnomasi (Asosiy)', \common\components\Config::LANGUAGE_UZBEK);
            $type->save();
        }
        if ($type = RatingGrade::findOne(['code' => RatingGrade::RATING_GRADE_SUBJECT_FINAL])) {
            $type->setTranslation('name', 'Fan qaydnomasi (Qo\'shimcha)', \common\components\Config::LANGUAGE_UZBEK);
            $type->save();
        }
    }

    public function safeDown()
    {

    }
}
