<?php

namespace common\models\system\classifier;

class EmployeeType extends _BaseClassifier
{
    const EMPLOYEE_TYPE_TEACHER = '12';

    public static function tableName()
    {
        return 'h_employee_type';
    }
}