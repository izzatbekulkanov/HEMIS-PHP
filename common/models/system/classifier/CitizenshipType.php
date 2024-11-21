<?php

namespace common\models\system\classifier;

class CitizenshipType extends _BaseClassifier
{
    const CITIZENSHIP_TYPE_UZB = '11';
    const CITIZENSHIP_TYPE_FOREIGN = '12';
    const CITIZENSHIP_TYPE_NOTCITIZENSHIP = '13';

    public static function tableName()
    {
        return 'h_citizenship_type';
    }
}