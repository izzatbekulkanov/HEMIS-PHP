<?php

namespace common\models\system\classifier;

class AcademicDegree extends _BaseClassifier
{
    const ACADEMIC_DEGREE_NONE = '10';
    const ACADEMIC_DEGREE_PHD = '11';
    const ACADEMIC_DEGREE_DSC = '12';
    public static function tableName()
    {
        return 'h_academic_degree';
    }
}