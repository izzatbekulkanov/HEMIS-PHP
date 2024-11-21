<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class StipendRate extends _BaseClassifier
{
    const STIPEND_RATE_OTHER = '10';
    const STIPEND_RATE_BASE = '11';
    const STIPEND_RATE_PRESIDENT = '12';
    const STIPEND_RATE_FAMOUS = '13';
    const STIPEND_RATE_GRANT_EXCELLENT = '14';
    const STIPEND_RATE_SCHOLAR_WITH_EXCELLENT = '15';
    const STIPEND_RATE_SCHOLAR_WITHOUT_EXCELLENT = '16';
    const STIPEND_RATE_INVALID = '17';
    const STIPEND_RATE_ORPHANAGE = '18';

    public static function tableName()
    {
        return 'h_stipend_rate';
    }

    public static function getBaseStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [self::STIPEND_RATE_BASE, self::STIPEND_RATE_GRANT_EXCELLENT, self::STIPEND_RATE_SCHOLAR_WITH_EXCELLENT, self::STIPEND_RATE_SCHOLAR_WITHOUT_EXCELLENT]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();
        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getFamousStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [self::STIPEND_RATE_PRESIDENT, self::STIPEND_RATE_FAMOUS]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();
        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getInvalidStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [self::STIPEND_RATE_INVALID]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();
        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getOrphanageStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [self::STIPEND_RATE_ORPHANAGE]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();
        return ArrayHelper::map($items, 'code', 'name');
    }
}