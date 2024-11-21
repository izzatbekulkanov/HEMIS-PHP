<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class ContractType extends _BaseClassifier
{
    const CONTRACT_TYPE_BASE = '11';
    const CONTRACT_TYPE_INCREASED_56 = '16';
    const CONTRACT_TYPE_INCREASED_1 = '17';
    const CONTRACT_TYPE_RECOMMEND = '18';
    const CONTRACT_TYPE_FOREIGN = '19';
    public static function tableName()
    {
        return 'h_contract_type';
    }

    public static function getBaseOptions()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => true])
            ->andWhere(['in', 'code', [self::CONTRACT_TYPE_BASE]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all(), 'code', 'name');
    }

    public static function getForeignOptions()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => true])
            ->andWhere(['in', 'code', [self::CONTRACT_TYPE_FOREIGN]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all(), 'code', 'name');
    }
}