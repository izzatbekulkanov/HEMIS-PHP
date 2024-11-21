<?php

namespace common\models\system\classifier;

use common\components\Config;
use common\models\system\_BaseModel;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * @property string $code
 * @property string $_parent
 * @property string $name
 * @property boolean $active
 * @property integer $position
 */
class _BaseClassifier extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    protected $_optionFields = ['version'];
    protected $_translatedAttributes = ['name'];

    public $items = [];
    public $version;
    public $processed = false;

    public static function getUniqueFieldName()
    {
        return 'code';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getDefaultCode()
    {
        $options = array_keys(self::getClassifierOptions());

        return array_shift($options);
    }

    public static function getClassifierOptions()
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getClassifierSpecialOptions($code)
    {
        $items = self::find()
            ->where(['active' => true])
            ->andWhere(['<>', 'code', $code])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }


    public static function getClassifierOptionsByName()
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderByTranslationField('name')
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getClassifierOptionsByNameWithCode()
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderByTranslationField('name')
            ->all();

        return ArrayHelper::map($items, 'code', function ($item) {
            return "{$item->code} - {$item->name}";
        });
    }

    public static function getParentClassifierOptions()
    {
        return [];
    }

    /**
     * @return self
     */
    public function getParentItem()
    {
        return null;
    }

    public function getParent()
    {
        return $this->hasOne(get_called_class(), ['code' => '_parent']);
    }

    public function getChildren()
    {
        return $this
            ->hasMany(get_called_class(), ['_parent' => 'code'])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['position'], 'number'],
            [['name', 'code'], 'required'],
            [['code'], 'unique'],
            [['_parent'], 'exist', 'targetAttribute' => 'code'],
            [['active'], 'safe'],
            [['code'], 'match', 'pattern' => '/^[a-z0-9_.]{2,255}$/i', 'message' => __('Use only alpha-number characters and underscore')],
        ]);
    }


    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'position',
                    'active',
                    'code',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->search) {
            foreach (Config::getShortLanguageCodes() as $code)
                $query->orWhereLike("name_$code", $this->search, '_translations');

            $query->orWhereLike('code', $this->search);
        }

        return $dataProvider;
    }

    public function beforeSave($insert)
    {
        if (empty($this->_parent))
            $this->_parent = null;

        if ($parent = $this->getParentItem()) {
            $this->_parent = $parent->primaryKey;
        }

        $options = [];
        foreach ($this->_optionFields as $field) {
            $options[$field] = $this->$field;
        }
        $this->_options = array_merge(is_array($this->_options) ? $this->_options : [], $options);

        $this->active = boolval($this->active);
        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        $options = $this->_options;
        foreach ($this->_optionFields as $field) {
            if (isset($options[$field])) {
                $this->$field = $options[$field];
            }
        }

        parent::afterFind(); // TODO: Change the autogenerated stub
    }

    public static function importOptionsFromApi($className, $items, &$position, &$count, &$options)
    {
        $uniqueField = $className::getUniqueFieldName();
        foreach ($items as $item) {
            /**
             * @var $option _BaseClassifier
             */

            if ($item[$uniqueField] == '0') {
                continue;
            }

            if (!isset($options[$item[$uniqueField]])) {
                $options[$item[$uniqueField]] = new $className();
            }

            $option = $options[$item[$uniqueField]];
            $option->processed = true;

            if (true || $item['version'] != $option->version || $option->version == null || $option->isNewRecord) {
                $option->active = boolval(isset($item['active']) ? $item['active'] : true);
                $option->code = trim($item['code']);
                $option->{$uniqueField} = trim($item[$uniqueField]);
                $option->name = isset($item['name']) ? $item['name'] : $item['name_uz'];

                $option->version = $item['version'];
                $option->position = $position;

                $option->setTranslation('name', $option->name, Config::LANGUAGE_UZBEK);

                if (isset($item['nameEn'])) {
                    $option->setTranslation('name', $item['nameEn'], Config::LANGUAGE_ENGLISH);
                }

                if (isset($item['nameRu'])) {
                    $option->setTranslation('name', $item['nameRu'], Config::LANGUAGE_RUSSIAN);
                }

                if (isset($item['name_ru']) && $item['name_ru']) {
                    $option->setTranslation('name', $item['name_ru'], Config::LANGUAGE_RUSSIAN);
                }

                if ($option->save(false)) {
                    $count++;
                }


                if (isset($item['parent']) && is_array($item['parent'])) {
                    //@todo parent
                    if (in_array(trim($className, "\\"), [BachelorSpeciality::class, MasterSpeciality::class, StudentSuccess::class])) {
                        if (!isset($options[$item['parent'][$uniqueField]])) {
                            $options[$item['parent'][$uniqueField]] = new $className();
                        }

                        $parent = $options[$item['parent'][$uniqueField]];

                        if (true || $item['parent']['version'] != $parent->version || $parent->version == null || $parent->isNewRecord) {
                            $parent->active = boolval(isset($item['parent']['active']) ? $item['parent']['active'] : true);
                            $parent->code = trim($item['parent']['code']);
                            $parent->{$uniqueField} = trim($item['parent'][$uniqueField]);
                            $parent->name = isset($item['parent']['name']) ? $item['parent']['name'] : $item['parent']['name_uz'];
                            $parent->version = $item['parent']['version'];
                            $parent->position = $position;

                            $parent->setTranslation('name', $parent->name, Config::LANGUAGE_UZBEK);

                            if (isset($item['parent']['name_ru']) && $item['parent']['name_ru']) {
                                $parent->setTranslation('name', $item['parent']['name_ru'], Config::LANGUAGE_RUSSIAN);
                            }

                            if (isset($item['parent']['nameRu']) && $item['parent']['nameRu']) {
                                $parent->setTranslation('name', $item['parent']['nameRu'], Config::LANGUAGE_RUSSIAN);
                            }

                            if (isset($item['parent']['nameEn']) && $item['parent']['nameEn']) {
                                $parent->setTranslation('name', $item['parent']['nameEn'], Config::LANGUAGE_ENGLISH);
                            }

                            if ($parent->save(false)) {
                                $count++;
                                $option->updateAttributes(['_parent' => $parent->{$uniqueField}]);
                            }
                        } else {
                            $option->updateAttributes(['_parent' => $item['parent'][$uniqueField]]);
                        }
                    }
                }

                if (isset($item['childs'])) {
                    self::importOptionsFromApi($className, $item['childs'], $position, $count, $options);
                }
            }
            $position++;
        }
        foreach ($options as $option) {
            if (!$option->processed) {
                $option->updateAttributes(['active' => false]);
            }
        }
    }

    public static function importData($cols, $pos = 0)
    {
        if (isset($cols[0]) && isset($cols[1]) && trim($cols[0]) && trim($cols[1])) {
            $model = self::findOne(['code' => trim($cols[0])]);
            $class = get_called_class();
            if (!$model) {
                $model = new $class;
                $model->active = true;
                $model->position = $pos;
            }

            $model->code = trim($cols[0]);
            $model->name = trim($cols[1]);

            $model->save(false);

            return $model;
        }
    }

    public static function importDataCols($cols, &$pos)
    {
        /**
         * @var $model _BaseClassifier
         */
        $pos++;
        $lang = Config::LANGUAGE_UZBEK;
        if (isset($cols['code']) && isset($cols[$lang]) && trim($cols['code']) && trim($cols[$lang])) {
            $code = trim($cols['code']);
            $name = trim($cols[$lang]);
            $model = self::findOne(['code' => $code]);
            $class = get_called_class();
            if (!$model) {
                $model = new $class;
                $model->active = true;
            }

            if (isset($cols['active'])) {
                $model->active = boolval($cols['active']);
            }

            if (isset($cols['parent'])) {
                $model->_parent = $cols['parent'];
            }

            $model->position = $pos;

            $model->code = $code;
            $model->name = $name;

            if (isset($cols['version'])) {
                $model->version = $cols['version'];
            }

            foreach (Config::getShortLanguageCodes() as $l => $c) {
                if (isset($cols[$l]) && $cols[$l]) {
                    $model->setTranslation('name', $cols[$l], $l);
                }
            }

            $model->save(false);

            return $model;
        }
    }

    public function getShortName()
    {
        return StringHelper::truncateWords($this->name, 6);
    }

    public function getOptionValue($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : null;
    }

    public function setOptionValue($key, $value)
    {
        $options = $this->_options;

        $options[$key] = $value;

        $this->_options = $options;
    }
}
