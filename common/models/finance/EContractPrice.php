<?php

namespace common\models\finance;

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Country;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ProjectCurrency;
use common\models\system\classifier\StudentType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_contract_price".
 *
 * @property int $id
 * @property int $_department
 * @property int $_specialty
 * @property string|null $_education_year
 * @property string $_education_type
 * @property string $_education_form
 * @property int $_contract_type
 * @property string|null $_country
 * @property string $_citizenship_type
 * @property string $_student_type
 * @property bool|null $_have_access_certificate
 * @property int|null $_minimum_wage
 * @property string $_contract_currency
 * @property float|null $coefficient
 * @property float $summa
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EContractType $contractType
 * @property EDepartment $department
 * @property EducationYear $educationYear
 * @property EMinimumWage $minimumWage
 * @property ESpecialty $specialty
 * @property CitizenshipType $citizenshipType
 * @property StudentType $studentType
 * @property Country $country
 * @property EducationForm $educationForm
 * @property EducationType $educationType
 * @property ProjectCurrency $contractCurrency
 */
class EContractPrice extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_LOCAL = 'create_local';
    const SCENARIO_CREATE_FOREIGN = 'create_foreign';
    const CONTRACT_LOCALITY_LOCAL = '11';
    const CONTRACT_LOCALITY_FOREIGN = '12';

    public static function tableName()
    {
        return 'e_contract_price';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getContractFormOptions()
    {
        return [
            self::CONTRACT_LOCALITY_LOCAL => __('Basic Contract'), // bazaviy shartnoma
            self::CONTRACT_LOCALITY_FOREIGN => __('Foreign Contract'), // xorijiy shartnoma
        ];
    }

    public static function getContractPrice($_department = false, $_specialty = false, $_education_form = false, $_student_type = false, $contract_locality= false)
    {
        $result="";
        $result = self::find()
            ->where([
                'active' => self::STATUS_ENABLE,
                '_department'=>$_department,
                '_specialty'=>$_specialty,
                '_education_form'=>$_education_form,
                'contract_locality'=>$contract_locality,
            ]);
            if($_student_type){
                $result->andWhere(['_student_type'=>$_student_type]);
            }
        $result = $result->one();

        return $result;
    }

    public static function getCheckContractPrice($_department = false, $_specialty = false, $_education_form = false, $_student_type = false, $contract_locality= false, $check="")
    {
        $result="";
        $result = self::find()
            ->where([
                'active' => self::STATUS_ENABLE,
                '_department'=>$_department,
                '_specialty'=>$_specialty,
                '_education_form'=>$_education_form,
                'contract_locality'=>$contract_locality,
            ]);
        if($_student_type){
            $result->andWhere(['_student_type'=>$_student_type]);
        }
        if($check !=""){
            $result->andWhere(['!=', 'id', $check]);
        }
        $result = $result->one();

        return $result;
    }


    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_department', '_specialty', '_education_form'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_department', '_specialty', '_education_form', 'coefficient', 'summa', '_student_type'], 'required', 'on' => self::SCENARIO_CREATE_LOCAL],
            [['_department', '_specialty', '_education_form', '_contract_currency', 'summa'], 'required', 'on' => self::SCENARIO_CREATE_FOREIGN],

            [['_department', '_specialty', '_minimum_wage', 'position'], 'default', 'value' => null],
            [['_department', '_specialty', '_minimum_wage', 'position'], 'integer'],
            [['_have_access_certificate', 'active'], 'boolean'],
            [['coefficient', 'summa'], 'number'],
            [['updated_at', 'created_at'], 'safe'],
            ['coefficient', 'compare', 'compareValue' => 0, 'operator' => '>', 'message'=> __('{attribute} must be greater than "0".')],
            [['_education_year', '_education_type', '_education_form', '_country', '_student_type', '_contract_currency', 'contract_locality', '_citizenship_type'], 'string', 'max' => 64],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_minimum_wage'], 'exist', 'skipOnError' => true, 'targetClass' => EMinimumWage::className(), 'targetAttribute' => ['_minimum_wage' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_citizenship_type'], 'exist', 'skipOnError' => true, 'targetClass' => CitizenshipType::className(), 'targetAttribute' => ['_citizenship_type' => 'code']],
            [['_student_type'], 'exist', 'skipOnError' => true, 'targetClass' => StudentType::className(), 'targetAttribute' => ['_student_type' => 'code']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_contract_currency'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectCurrency::className(), 'targetAttribute' => ['_contract_currency' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
                'summa' => __('Contract Price Summa'),
            ]
        );
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getMinimumWage()
    {
        return $this->hasOne(EMinimumWage::className(), ['id' => '_minimum_wage']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getCitizenshipType()
    {
        return $this->hasOne(CitizenshipType::className(), ['code' => '_citizenship_type']);
    }

    public function getStudentType()
    {
        return $this->hasOne(StudentType::className(), ['code' => '_student_type']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getContractCurrency()
    {
        return $this->hasOne(ProjectCurrency::className(), ['code' => '_contract_currency']);
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
                    '_education_form',
                    '_country',
                    '_citizenship_type',
                    '_student_type',
                    '_have_access_certificate',
                    '_minimum_wage',
                    '_contract_currency',
                    'coefficient',
                    'summa',
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
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_country) {
            $query->andFilterWhere(['_country' => $this->_country]);
        }
        if ($this->_citizenship_type) {
            $query->andFilterWhere(['_citizenship_type' => $this->_citizenship_type]);
        }
        if ($this->_student_type) {
            $query->andFilterWhere(['_student_type' => $this->_student_type]);
        }
        if ($this->_minimum_wage) {
            $query->andFilterWhere(['_minimum_wage' => $this->_minimum_wage]);
        }
        if ($this->_contract_currency) {
            $query->andFilterWhere(['_contract_currency' => $this->_contract_currency]);
        }
        return $dataProvider;
    }
}
