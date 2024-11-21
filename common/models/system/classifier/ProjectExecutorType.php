<?php

namespace common\models\system\classifier;

class ProjectExecutorType extends _BaseClassifier
{
    const PROJECT_EXECUTOR_TYPE_OTHER = '10';
    const PROJECT_EXECUTOR_TYPE_TEACHER = '11';
    const PROJECT_EXECUTOR_TYPE_RESEARCHER = '12';
    const PROJECT_EXECUTOR_TYPE_STUDENT = '13';

    public static function tableName()
    {
        return 'h_project_executor_type';
    }
}