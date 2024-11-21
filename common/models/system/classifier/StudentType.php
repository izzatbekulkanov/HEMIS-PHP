<?php

namespace common\models\system\classifier;

class StudentType extends _BaseClassifier
{
    const STUDENT_TYPE_SIMPLE = '11';
    const STUDENT_TYPE_SUPER_CONTRACT = '13';
    public static function tableName()
    {
        return 'h_student_type';
    }
}