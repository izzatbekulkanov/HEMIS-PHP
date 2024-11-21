<?php

namespace common\models\system\classifier;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

class Soato extends _BaseClassifier
{
    public static function tableName()
    {
        return 'h_soato';
    }

    public static function getClassifierOptions()
    {
        $items = self::find()
            ->with(['children'])
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['=', new Expression('length(code)'), 4])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        $data = ArrayHelper::map($items, 'name', function ($item) {
            $items = [];
            foreach ($item->children as $ch) {
                $items[$ch->code] = $ch->name;
            }
            return $items;
        });

        return $data;
    }

    public static function getParentClassifierOptions()
    {
        $items = self::find()
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['=', new Expression('length(code)'), 4])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getChildrenOption($parent = false)
    {
        $items = self::find()
            ->andFilterWhere(['!=', 'code', $parent])
            ->andFilterWhere(['_parent' => $parent, 'active' => true])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return $items;
    }


    public static function importDataCols($cols, &$pos)
    {
        if ($model = parent::importDataCols($cols, $pos)) {
            self::updateParent($model);
        }

        return $model;
    }


    public static function importData($cols, $pos = 0)
    {
        if ($model = parent::importData($cols, $pos)) {
            self::updateParent($model);
        }

        return $model;
    }


    private static function updateParent($model)
    {
        if (strlen($model->code) > 4) {
            $code = substr($model->code, 0, 4);
            if ($parent = self::findOne(['code' => $code])) {
                $model->updateAttributes(['_parent' => $parent->code]);
            }
        }
    }

    public function getParentItem()
    {
        $code = substr($this->code, 0, 4);
        return self::findOne(['code' => $code]);
    }
}