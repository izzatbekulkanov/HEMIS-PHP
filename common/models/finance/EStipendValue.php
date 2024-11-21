<?php

namespace common\models\finance;

use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\StipendRate;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_stipend_value".
 *
 * @property int $id
 * @property string $_stipend_rate
 * @property float $stipend_value
 * @property string|null $begin_date
 * @property string|null $document
 * @property int|null $current_status
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property HStipendRate $stipendRate
 */
class EStipendValue extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    //public $_stipend_rate;
    public static function tableName()
    {
        return 'e_stipend_value';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getBaseStipendRate()
    {
        return self::find()
            ->where(['active' => self::STATUS_ENABLE, '_stipend_rate' => StipendRate::STIPEND_RATE_BASE])
            ->one();
    }

    public static function getStipendRateValue($stipendRate = false)
    {
        return self::find()
            ->where(['active' => self::STATUS_ENABLE, '_stipend_rate' => $stipendRate])
            ->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_stipend_rate', 'stipend_value', 'begin_date'], 'required', 'on' => self::SCENARIO_CREATE],
            [['stipend_value'], 'number'],
            [['begin_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['current_status', 'position'], 'default', 'value' => null],
            [['current_status', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_stipend_rate'], 'string', 'max' => 64],
            [['document'], 'string', 'max' => 1024],
            [['_stipend_rate'], 'exist', 'skipOnError' => true, 'targetClass' => StipendRate::className(), 'targetAttribute' => ['_stipend_rate' => 'code']],
        ]);
    }

    public function getStipendRate()
    {
        return $this->hasOne(StipendRate::className(), ['code' => '_stipend_rate']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    '_stipend_rate',
                    'stipend_value',
                    'begin_date',
                    'document',
                    'current_status',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_stipend_rate) {
            $query->andFilterWhere(['_stipend_rate' => $this->_stipend_rate]);
        }
        return $dataProvider;
    }
}
