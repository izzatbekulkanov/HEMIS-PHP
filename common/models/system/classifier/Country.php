<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class Country extends _BaseClassifier
{
    const COUNTRY_CODE_uz = 'uz';
    const COUNTRY_CODE_UZ_upper = 'UZ';
    public static function tableName()
    {
        return 'h_country';
    }

    public static function getClassifierOwnerOptions()
    {
        $items = self::find()
            ->where(['active' => true])
            ->andWhere(['NOT IN', 'code', [self::COUNTRY_CODE_uz, self::COUNTRY_CODE_UZ_upper]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }
}