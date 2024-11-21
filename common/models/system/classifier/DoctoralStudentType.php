<?php

namespace common\models\system\classifier;

class DoctoralStudentType extends _BaseClassifier
{
    const TYPE_PHD_DOC = '11';
    const TYPE_PHD_ASP = '12';
    const TYPE_DSC_DOC = '13';
    const TYPE_DSC_ASP = '14';

    public static function tableName()
    {
        return 'h_doctoral_student_type';
    }

    public function getEducationType()
    {
        return $this->code == self::TYPE_PHD_ASP || $this->code == self::TYPE_PHD_DOC ? EducationType::EDUCATION_TYPE_PHD : EducationType::EDUCATION_TYPE_DSC;
    }
}