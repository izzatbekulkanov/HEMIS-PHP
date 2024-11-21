<?php

namespace common\models\system\classifier;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

class StudentSuccess extends _BaseClassifier
{
    public static function tableName()
    {
        return 'h_student_success';
    }

    public static function getClassifierOptions()
    {
        $items = self::find()
            ->with(['children'])
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['=', new Expression('length(code)'), 2])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        $data = ArrayHelper::map($items, 'code', 'name');

        return $data;
    }

    public static function getParentClassifierOptions()
    {
        $items = self::find()
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['=', new Expression('length(code)'), 1])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getChildrenOption($parent = false)
    {
        $items = self::find()
            ->andFilterWhere(['!=', 'code', $parent])
            ->andFilterWhere(['=', new Expression('length(code)'), 2])
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
        if (strlen($model->code) > 1) {
            if ($parent = self::findOne(['code' => $model->_parent])) {
                $model->updateAttributes(['_parent' => $parent->code]);
            }
        }
    }

    public function getParentItem()
    {
        return self::findOne(['code' => $this->_parent]);
    }
}