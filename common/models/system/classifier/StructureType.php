<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class StructureType extends _BaseClassifier
{
    const STRUCTURE_TYPE_FACULTY = '11';
    const STRUCTURE_TYPE_DEPARTMENT = '12';

    public static function tableName()
    {
        return 'h_structure_type';
    }

    public static function getSectionOptions()
    {
        return ArrayHelper::map(StructureType::find()
            ->where(['active' => true])
            ->andWhere(['not in', 'code', [self::STRUCTURE_TYPE_FACULTY, self::STRUCTURE_TYPE_DEPARTMENT]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all(), 'code', 'name');
    }


    public static function importData($cols, $pos = 0)
    {
        if (isset($cols[0]) && $cols[0] > 0) {
            return parent::importData($cols, $pos);
        }
    }

    public static function importDataCols($cols, &$pos)
    {
        if (isset($cols['code']) && $cols['code'] > 0) {
            return parent::importDataCols($cols, $pos);
        }
    }
}