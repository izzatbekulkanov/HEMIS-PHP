<?php

namespace common\models\system\classifier;

class SemestrType extends _BaseClassifier
{
    const EDUCATION_TYPE_AUTUMN = '11';
    const EDUCATION_TYPE_SPRING = '12';

    public static function tableName()
    {
        return 'h_semestr_type';
    }
}