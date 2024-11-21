<?php

namespace common\models\system\classifier;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

class SubjectBlock extends _BaseClassifier
{
    public static function tableName()
    {
        return 'h_subject_block';
    }


    public static function getClassifierOptions()
    {
        $items = self::find()
            ->with(['children'])
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['=', new Expression('length(code)'), 2])
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
            ->andFilterWhere(['=', new Expression('length(code)'), 2])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }


    public static function importData($cols, $pos = 0)
    {
        if ($model = parent::importData($cols, $pos)) {
            self::updateParent($model);
        }

        return $model;
    }


    public static function importDataCols($cols, &$pos)
    {
        if ($model = parent::importDataCols($cols, $pos)) {
            self::updateParent($model);
        }

        return $model;
    }


    private static function updateParent($model)
    {
        if (strlen($model->code) > 2) {
            $code = substr($model->code, 0, 2);
            if ($parent = self::findOne(['code' => $code])) {
                $model->updateAttributes(['_parent' => $parent->code]);
            }
        }
    }

    public function getParentItem()
    {
        $code = substr($this->code, 0, 2);
        return self::findOne(['code' => $code]);
    }
}