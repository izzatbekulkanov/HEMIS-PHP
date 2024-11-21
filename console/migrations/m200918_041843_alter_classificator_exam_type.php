<?php

use common\models\system\classifier\ExamType;
use yii\db\Migration;

/**
 * Class m200918_041843_alter_classificator_exam_type
 */
class m200918_041843_alter_classificator_exam_type extends Migration
{
    public function safeUp()
    {
        if ($type = ExamType::findOne(ExamType::EXAM_TYPE_CURRENT_FIRST)) {
            $type->updateAttributes(['_parent' => ExamType::EXAM_TYPE_CURRENT]);
        }
        if ($type = ExamType::findOne(ExamType::EXAM_TYPE_CURRENT_SECOND)) {
            $type->updateAttributes(['_parent' => ExamType::EXAM_TYPE_CURRENT]);
        }
        if ($type = ExamType::findOne(ExamType::EXAM_TYPE_MIDTERM_FIRST)) {
            $type->updateAttributes(['_parent' => ExamType::EXAM_TYPE_MIDTERM]);
        }
        if ($type = ExamType::findOne(ExamType::EXAM_TYPE_MIDTERM_SECOND)) {
            $type->updateAttributes(['_parent' => ExamType::EXAM_TYPE_MIDTERM]);
        }
    }


    public function safeDown()
    {
    }

}
