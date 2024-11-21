<?php

namespace common\models\system\classifier;

class EducationForm extends _BaseClassifier
{
    const EDUCATION_FORM_DAYLY = '11';
    const EDUCATION_FORM_EVENING = '12';
    const EDUCATION_FORM_SECOND_HIGHER_DAYLY = '18';

    const EDUCATION_FORM_PART_TIME = '13';
    const EDUCATION_FORM_SPECIAL = '14';
    const EDUCATION_FORM_SECOND_HIGHER_PART_TIME = '15';

    public static function tableName()
    {
        return 'h_education_form';
    }
}