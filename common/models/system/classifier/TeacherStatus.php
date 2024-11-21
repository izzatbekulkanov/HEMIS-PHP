<?php

namespace common\models\system\classifier;

class TeacherStatus extends _BaseClassifier
{
    const TEACHER_STATUS_WORKING = '11';

    public static function tableName()
    {
        return 'h_teacher_status';
    }
}