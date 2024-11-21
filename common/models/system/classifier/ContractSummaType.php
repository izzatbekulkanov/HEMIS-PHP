<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class ContractSummaType extends _BaseClassifier
{
    const CONTRACT_SUMMA_TYPE_ON = 11;
    const CONTRACT_SUMMA_TYPE_OFF = 12;
    public static function tableName()
    {
        return 'h_contract_summa_type';
    }

    public static function getClassifierOtherOptions()
    {
        $items = self::find()
            ->where(['active' => true])
            ->andWhere(['<>', 'code', '11'])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }
    public static function getSortClassifierOptions()
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderBy(['position' => SORT_DESC, 'code' => SORT_DESC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

}