<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class FinalExamType extends _BaseClassifier
{
    const FINAL_EXAM_TYPE_FIRST = '11';
    const FINAL_EXAM_TYPE_SECOND = '12';
    const FINAL_EXAM_TYPE_THIRD = '13';

    public static function tableName()
    {
        return 'h_final_exam_type';
    }

    public static function getFinalExamTypeOptions($final_exam_count)
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->limit($final_exam_count)
            ->all();
        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getFinalExamTypeIds()
    {
        return [
            self::FINAL_EXAM_TYPE_FIRST => __('Main Mark'),
            self::FINAL_EXAM_TYPE_SECOND =>__('2-resubmission'),
            self::FINAL_EXAM_TYPE_THIRD => __('3-resubmission'),
        ];
    }

}