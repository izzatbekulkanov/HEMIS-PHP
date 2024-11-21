<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class EducationYear extends _BaseClassifier
{
    public static function tableName()
    {
        return 'h_education_year';
    }


    public static function getClassifierOptions()
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderBy(['code' => SORT_ASC, 'position' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', function ($item) {
            return $item->code . ' (' . $item->name . ')';
        });
    }

}