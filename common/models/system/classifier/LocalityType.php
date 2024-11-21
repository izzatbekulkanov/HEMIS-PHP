<?php

namespace common\models\system\classifier;

class LocalityType extends _BaseClassifier
{
    public const TYPE_LOCAL = '11';
    public const TYPE_INCORPORATE = '12';

    public static function tableName()
    {
        return 'h_locality_type';
    }
}