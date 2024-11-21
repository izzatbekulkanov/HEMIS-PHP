<?php

namespace common\models\system\classifier;

class Course extends _BaseClassifier
{
    const COURSE_FIRST = '11';
    const COURSE_SECOND = '12';
    const COURSE_THIRD = '13';
    const COURSE_FOURTH = '14';
    public static function tableName()
    {
        return 'h_course';
    }
}