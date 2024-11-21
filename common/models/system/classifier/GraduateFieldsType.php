<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class GraduateFieldsType extends _BaseClassifier
{
    const GRADUATE_FIELD_STUDY_CONTINUED = '30';

    public static function tableName()
    {
        return 'h_graduate_fields_type';
    }

    public static function getFieldTypeOptions($study = false)
    {
        if(!$study){
            $items = self::find()
                ->where(['active' => true])
                ->andWhere([ 'code' => self::GRADUATE_FIELD_STUDY_CONTINUED])
                ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all();
        }
        else{
            $items = self::find()
                ->where(['active' => true])
                ->andWhere(['<>', 'code', self::GRADUATE_FIELD_STUDY_CONTINUED])
                ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all();
        }
        return $items;
    }
}