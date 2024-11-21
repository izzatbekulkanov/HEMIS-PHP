<?php

namespace common\models\system\classifier;

class SubjectType extends _BaseClassifier
{
    const SUBJECT_TYPE_REQUIRED = '11';
    const SUBJECT_TYPE_SELECTION = '12';

    public static function tableName()
    {
        return 'h_subject_type';
    }
}