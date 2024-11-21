<?php

namespace common\models\system\classifier;

use common\components\Config;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class BachelorSpeciality extends _BaseClassifier
{
    public static function tableName()
    {
        return 'h_bachelor_speciality';
    }

    public static function getUniqueFieldName()
    {
        return 'id';
    }

    public $type;
    public $year;

    protected $_optionFields = ['type', 'year', 'version'];

    public static function getTypeOptions()
    {
        return LocalityType::getClassifierOptions();
    }

    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['_parent'], 'exist', 'targetAttribute' => 'id'],
            [['active'], 'safe'],
            [['code'], 'match', 'pattern' => '/^[a-zA-Z0-9_.]{2,255}$/i', 'message' => __('Use only alpha-number characters and underscore')],
            [['year', 'type', 'search'], 'safe'],
            [['type'], 'required'],
            [['year'], 'number'],
            [['type'], 'in', 'range' => array_keys(self::getTypeOptions())],
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->type == null) {
            $this->type = LocalityType::TYPE_LOCAL;
        }
        if ($this->hasAttribute('id')) {
            if ($this->id == null) {
                $this->id = gen_uuid();
            }
        }

        return parent::beforeSave($insert);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'type' => __('Type'),
            'year' => __('Year'),
        ]);
    }


    public static function getClassifierOptions()
    {
        $items = self::find()
            ->with(['children'])
            ->andFilterWhere(['active' => true])
            ->andWhere(new Expression("code like '%0000'"))
            ->andWhere(new Expression("length(code) = 6"))
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
            ->andWhere(new Expression("code like '%0000'"))
            ->andWhere(new Expression("length(code) = 6"))
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


    public static function importData($cols, $pos = 0)
    {
        if ($model = parent::importData($cols, $pos)) {
            self::updateParent($model);
        }

        return $model;
    }

    public static function importDataCols($cols, &$pos)
    {
        if (HEMIS_INTEGRATION == false || 1) {
            if ($model = parent::importDataCols($cols, $pos)) {
                self::updateParent($model);
            }

            return $model;
        }
        return false;
    }

    private static function updateParent($model)
    {
        $code = mb_substr($model->code, 1, 2);
        if ($parent = self::findOne(['code' => $code . '0000'])) {
            if ($parent->code != $model->code) {
                $model->updateAttributes(['_parent' => $parent->primaryKey]);
            }
        }
    }

    public function getParentItem()
    {
        $code = mb_substr(trim($this->code), 1, 2) . '0000';
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