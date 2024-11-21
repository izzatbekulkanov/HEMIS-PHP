<?php

namespace common\models\system\classifier;

class Gender extends _BaseClassifier
{
    const GENDER_MALE = '11';
    const GENDER_FEMALE = '12';
    const GENDER_NONE = '10';
	public static function tableName()
    {
        return 'h_gender';
    }
}