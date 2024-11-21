<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class EducationType extends _BaseClassifier
{
    const EDUCATION_TYPE_BACHELOR = '11';
    const EDUCATION_TYPE_MASTER = '12';
    const EDUCATION_TYPE_TRAINEESHIP = '13'; //ordinatura
    const EDUCATION_TYPE_PHD = '14'; //	Doktorantura PhD
    const EDUCATION_TYPE_DSC = '15'; //Doktorantura DSc

    public static function tableName()
    {
        return 'h_education_type';
    }

    public static function getHighers()
    {
        return ArrayHelper::map(EducationType::find()
            ->where(['active' => true])
            ->andWhere(['in', 'code', [
                self::EDUCATION_TYPE_BACHELOR,
                self::EDUCATION_TYPE_MASTER,
            ]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all(), 'code', 'name');
    }
}