<?php

namespace common\models\finance;

use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\ContractType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_contract_type".
 *
 * @property int $id
 * @property string $name
 * @property float $coef
 * @property int|null $current_status
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EContractPrice[] $eContractPrices
 * @property EIncreasedContractCoefficient[] $eIncreasedContractCoefficients
 */
class EContractType extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    public static function tableName()
    {
        return 'e_contract_type';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getContractTypeByType($_contract_type = false)
    {
        return self::find()
            ->where(['active' => self::STATUS_ENABLE, '_contract_type' => $_contract_type])
            ->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_contract_type', 'coef'], 'required', 'on' => self::SCENARIO_CREATE],
            [['coef'], 'number'],
            [['current_status', 'position'], 'default', 'value' => null],
            [['current_status', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_contract_type'], 'string', 'max' => 64],
            [['_contract_type'], 'exist', 'skipOnError' => true, 'targetClass' => ContractType::className(), 'targetAttribute' => ['_contract_type' => 'code']],
        ]);
    }

    public function getEContractPrices()
    {
        return $this->hasMany(EContractPrice::className(), ['_contract_type' => 'id']);
    }

    public function getEIncreasedContractCoefficients()
    {
        return $this->hasMany(EIncreasedContractCoefficient::className(), ['_contract_type' => 'id']);
    }

    public function getContractType()
    {
        return $this->hasOne(ContractType::className(), ['code' => '_contract_type']);
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
                    'coef',
                    '_contract_type',
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
        /*if ($this->search) {
            // $query->orWhereLike('name_uz', $this->search, '_translations');
            //  $query->orWhereLike('name_oz', $this->search, '_translations');
            //  $query->orWhereLike('name_ru', $this->search, '_translations');
               $query->orWhereLike('coef', $this->search);
        }*/
        if ($this->search) {
            $query->andFilterWhere(['coef' => $this->search]);
        }
        if ($this->_contract_type) {
            $query->andFilterWhere(['_contract_type' => $this->_contract_type]);
        }

        return $dataProvider;
    }
}
