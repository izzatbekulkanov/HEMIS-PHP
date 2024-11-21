<?php

namespace common\models\system\classifier;

class ExamFinish extends _BaseClassifier
{
    const EXAM_FINISH_EXAM = '11';
    public static function tableName()
    {
        return 'h_exam_finish';
    }
}