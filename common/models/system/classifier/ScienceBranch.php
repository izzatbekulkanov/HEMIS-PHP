<?php

namespace common\models\system\classifier;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

class ScienceBranch extends _BaseClassifier
{
    public static function tableName()
    {
        return 'h_science_branch';
    }

    public static function getUniqueFieldName()
    {
        return 'id';
    }

    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['_parent'], 'exist', 'targetAttribute' => 'id'],
            [['active'], 'safe'],
            [['code'], 'match', 'pattern' => '/^[a-zA-Z0-9_.]{2,255}$/i', 'message' => __('Use only alpha-number characters and underscore')],
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->hasAttribute('id')) {
            if ($this->id == null) {
                $this->id = gen_uuid();
            }
        }

        return parent::beforeSave($insert);
    }

    public static function getClassifierOptions()
    {
        $items = self::find()
            ->with(['children'])
            ->andFilterWhere(['active' => true])
            ->andWhere(new Expression("code like '%.00.00'"))
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
            ->andWhere(new Expression("code like '%.00.00'"))
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'id', 'name');
    }

    public static function getChildClassifierOptions()
    {
        $items = self::find()
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['not in', '_parent', 'null'])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();
        return ArrayHelper::map($items, 'id', 'fullName');
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
        $code = mb_substr($model->code, 0, 2);
        if ($parent = self::findOne(['code' => $code . '.00.00'])) {
            if ($parent->code != $model->code) {
                $model->updateAttributes(['_parent' => $parent->primaryKey]);
            }
        }
    }

    public function getParentItem()
    {
        $code = mb_substr($this->code, 0, 2) . '.00.00';
        if ($parent = self::findOne(['code' => $code])) {
            if ($parent->code != $this->code) {
                return $parent;
            }
        }

        return null;
    }

    public function getFullName()
    {
        return $this->code . ' - ' . $this->name;
    }
}