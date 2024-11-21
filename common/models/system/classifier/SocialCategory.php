<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class SocialCategory extends _BaseClassifier
{
    const SOCIAL_CATEGORY_OTHER = '10';
    const SOCIAL_CATEGORY_ORPHANAGE= '11';
    const SOCIAL_CATEGORY_INVALID= '12';
    const SOCIAL_CATEGORY_PARENTAL_CARE= '13';
    const SOCIAL_CATEGORY_CHILD_OF_MILITARY= '14';

    public static function tableName()
    {
        return 'h_social_category';
    }

    /*public static function getBaseStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [SocialCategory::SOCIAL_CATEGORY_OTHER, SocialCategory::SOCIAL_CATEGORY_ORPHANAGE, SocialCategory::SOCIAL_CATEGORY_INVALID]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }*/
}