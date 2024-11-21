<?php

namespace common\models\system\classifier;

class ProjectCurrency extends _BaseClassifier
{
    const CURRENCY_TYPE_UZB = '11';
    public static function tableName()
    {
        return 'h_project_currency';
    }
}