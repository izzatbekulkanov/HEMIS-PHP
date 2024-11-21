<?php

namespace common\models\finance;

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\ContractSummaType;
use common\models\system\classifier\EducationForm;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_contract_type".
 *
 * @property int $id
 * @property int $_specialty
 * @property int $_student
 * @property string|null $_education_year
 * @property string $_education_form
 * @property string|null $_contract_summa_type
 * @property string|null $contract_form_type
 * @property bool|null $_created_self
 * @property string|null $contract_status
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEducationYear $educationYear
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property HContractSummaType $contractSummaType
 * @property HEducationForm $educationForm
 */
class EStudentContractType extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_SELF = 'create_student';

    const CONTRACT_FORM_TWO = '11';
    const CONTRACT_FORM_THREE = '12';

    const CONTRACT_REQUEST_STATUS_SEND = '11';
    const CONTRACT_REQUEST_STATUS_PROCESS = '12';
    const CONTRACT_REQUEST_STATUS_READY = '13';
    const CONTRACT_REQUEST_STATUS_GENERATED = '14';
    public $_contract_type;
    public static function tableName()
    {
        return 'e_student_contract_type';
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
            self::CONTRACT_FORM_TWO => __('Bilateral Contract'), // 2 tomonlama shartnoma
            self::CONTRACT_FORM_THREE => __('Three-sided Contract'), // 3 tomonlama shartnoma
        ];
    }

    public static function getContractFormBileteralOptions()
    {
        return [
            self::CONTRACT_FORM_TWO => __('Bilateral Contract'), // 2 tomonlama shartnoma
        ];
    }

    public function getContractFormLabel()
    {
        return self::getContractFormOptions()[$this->contract_form_type];
    }

    public static function getContractStatusOptions()
    {
        return [
            self::CONTRACT_REQUEST_STATUS_SEND => __('Request send for contract'), // so'rov yuborildi
            self::CONTRACT_REQUEST_STATUS_PROCESS => __('In the process of preparation contract'), // Jarayonda
            self::CONTRACT_REQUEST_STATUS_READY => __('The contract is ready'), // Tayyor
            self::CONTRACT_REQUEST_STATUS_GENERATED => __('The contract is generated'), // Generatsiya qilingan

        ];
    }

    public static function getContractType($_specialty = false, $_student= false, $_education_form = false)
    {
        $result="";
        $result = self::find()
            ->where([
                'active' => self::STATUS_ENABLE,
                '_specialty'=>$_specialty,
                '_student'=>$_student,
                '_education_form'=>$_education_form,
            ])
            ->one();

        return $result;
    }
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_specialty', '_student', '_education_form'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_education_year', '_contract_type', '_contract_summa_type', 'contract_form_type'], 'required', 'on' => self::SCENARIO_CREATE_SELF],
            [['_specialty', '_student', 'position'], 'default', 'value' => null],
            [['_department', '_specialty', '_student', 'position'], 'integer'],
            [['_created_self', 'active'], 'boolean'],
            [['updated_at', 'created_at'], 'safe'],
            [['_education_year', '_education_form', '_contract_summa_type', 'contract_form_type', 'contract_status'], 'string', 'max' => 64],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_contract_summa_type'], 'exist', 'skipOnError' => true, 'targetClass' => ContractSummaType::className(), 'targetAttribute' => ['_contract_summa_type' => 'code']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
        ]);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getContractSummaType()
    {
        return $this->hasOne(ContractSummaType::className(), ['code' => '_contract_summa_type']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
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
                    '_specialty',
                    '_department',
                    '_student',
                    '_education_year',
                    '_education_form',
                    '_contract_summa_type',
                    'contract_form_type',
                    '_created_self',
                    'contract_status',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_contract_summa_type) {
            $query->andFilterWhere(['_contract_summa_type' => $this->_contract_summa_type]);
        }
        if ($this->contract_form_type) {
            $query->andFilterWhere(['contract_form_type' => $this->contract_form_type]);
        }
        if ($this->contract_status) {
            $query->andFilterWhere(['contract_status' => $this->contract_status]);
        }
        return $dataProvider;
    }

}
