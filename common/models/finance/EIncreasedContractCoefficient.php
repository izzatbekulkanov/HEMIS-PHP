<?php

namespace common\models\finance;

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_increased_contract_coefficient".
 *
 * @property int $id
 * @property int $_department
 * @property int $_specialty
 * @property string $_education_year
 * @property int $_contract_type
 * @property float|null $coefficient
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EContractType $contractType
 * @property EDepartment $department
 * @property EEducationYear $educationYear
 * @property ESpecialty $specialty
 */
class EIncreasedContractCoefficient extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    public static function tableName()
    {
        return 'e_increased_contract_coefficient';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getContractCoef($_department = false, $_specialty = false)
    {
        $result="";
        $result = self::find()
            ->where([
                'active' => self::STATUS_ENABLE,
                '_department'=>$_department,
                '_specialty'=>$_specialty,
                //'_education_form'=>$_education_form,
            ])
            ->one();

        return $result;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_department', '_specialty', 'coefficient'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_department', '_specialty', '_contract_type', 'position'], 'default', 'value' => null],
            [['_department', '_specialty', '_contract_type', 'position'], 'integer'],
            [['coefficient'], 'number'],
            [['active'], 'boolean'],
            [['updated_at', 'created_at'], 'safe'],
            [['_education_year', '_education_type'], 'string', 'max' => 64],
            ['coefficient', 'compare', 'compareValue' => 0, 'operator' => '>', 'message'=> __('{attribute} must be greater than "0".')],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_contract_type'], 'exist', 'skipOnError' => true, 'targetClass' => EContractType::className(), 'targetAttribute' => ['_contract_type' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
            ]
        );
    }

    public function getContractType()
    {
        return $this->hasOne(EContractType::className(), ['id' => '_contract_type']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
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
                    '_department',
                    '_specialty',
                    '_education_year',
                    '_education_type',
                    '_contract_type',
                    'coefficient',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_contract_type) {
            $query->andFilterWhere(['_contract_type' => $this->_contract_type]);
        }
        return $dataProvider;
    }
}
