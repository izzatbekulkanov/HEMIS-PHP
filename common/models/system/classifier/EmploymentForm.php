<?php

namespace common\models\system\classifier;

class EmploymentForm extends _BaseClassifier
{
    const EMPLOYMENT_FORM_MAIN = '11';
    const EMPLOYMENT_FORM_INDOOR = '12';
    const EMPLOYMENT_FORM_OUTDOOR = '13';
    const EMPLOYMENT_FORM_TIMEBY = '14';

    public static function tableName()
    {
        return 'h_employment_form';
    }
}