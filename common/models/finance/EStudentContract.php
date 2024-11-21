<?php

namespace common\models\finance;

use common\components\Config;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\ContractSummaType;
use common\models\system\classifier\ContractType;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentStatus;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "e_student_contract".
 *
 * @property int $id
 * @property string|null $number
 * @property string $date
 * @property string $hash
 * @property float $summa
 * @property string|null $_contract_summa_type
 * @property string|null $contract_form_type
 * @property string|null $_education_year
 * @property int $_student
 * @property int $_specialty
 * @property string $_education_type
 * @property string $_education_form
 * @property string|null $university_code
 * @property string|null $rector
 * @property string|null $mailing_address
 * @property string|null $bank_details
 * @property string|null $contract_status
 * @property string|null $customer
 * @property int|null $position
 * @property int|null $education_period
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EducationYear $educationYear
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property ContractSummaType $contractSummaType
 * @property EducationForm $educationForm
 * @property EducationType $educationType
 */
class EStudentContract extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_CHANGE_TYPE = 'change_type';
    const SCENARIO_CONTRACT_MANUAL = 'contract_manual';

    const DIFFERENT_DEBTOR_STATUS = '11';
    const DIFFERENT_NOT_DEBTOR_STATUS = '12';

    const DIFFERENT_EQUAL_STATUS = '10';
    const MANUAL_STATUS_TYPE_AUTO = '11';
    const MANUAL_STATUS_TYPE_MANUAL = '12';

    const GRADUATE_TYPE_NO = '11';
    const GRADUATE_TYPE_YES = '12';

    const CONTRACT_CALCULATION_COEFFICIENT = '11';
    const CONTRACT_CALCULATION_SUM = '12';
    public $_students;

    //public $_contract_type;

    public static function tableName()
    {
        return 'e_student_contract';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getDifferentOptions()
    {
        return [
            self::DIFFERENT_DEBTOR_STATUS => __('Contract Debtor'),
            self::DIFFERENT_NOT_DEBTOR_STATUS => __('Contract nodebtor'),
        ];
    }

    public static function getGraduateTypeOptions()
    {
        return [
            self::GRADUATE_TYPE_NO => __('Not a Graduate Student'),
            self::GRADUATE_TYPE_YES => __('Graduate Student'),
        ];
    }

    public static function getContractCalculationTypeOptions()
    {
        return [
            self::CONTRACT_CALCULATION_COEFFICIENT => __('By Coefficient'),
            self::CONTRACT_CALCULATION_SUM => __('By Sum'),
        ];
    }

    public static function getCountContract($_education_year = false)
    {
        $result = "";
        $exist = self::find()
            ->where(['_education_year' => $_education_year])
            ->andWhere(['in', '_contract_type', [ContractType::CONTRACT_TYPE_BASE, ContractType::CONTRACT_TYPE_RECOMMEND, ContractType::CONTRACT_TYPE_FOREIGN]])
            ->andWhere(['not', ['number' => null]])
            ->count();
        $exist = $exist + 1;
        if ($exist < 10)
            $result = '0000' . ($exist);
        elseif ($exist < 100)
            $result = '000' . ($exist);
        elseif ($exist < 1000)
            $result = '00' . ($exist);
        elseif ($exist < 10000)
            $result = '0' . ($exist);
        elseif ($exist < 100000)
            $result = ($exist);
        return $result;
    }

    public static function validateNumber(EStudentContract $contract, $number)
    {
        $contract_number = self::find()
            ->where(['number' => $number])
            ->andWhere(['not', ['id' => $contract->id]])
            ->one();
        if ($contract_number !== null) {
            $number = $number + 1;
            return self::validateNumber($contract_number, $number);
        } else
            return $number;
    }

    public static function getContract($_specialty = false, $_student = false, $_education_form = false, $_education_year = false)
    {
        $result = "";
        $result = self::find()
            ->where([
                'active' => self::STATUS_ENABLE,
                '_specialty' => $_specialty,
                '_student' => $_student,
                '_education_form' => $_education_form,
                '_education_year' => $_education_year,
            ])
            ->one();

        return $result;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['contract_form_type', '_contract_summa_type', '_contract_type', 'mailing_address', 'bank_details', 'month_count', '_education_year', '_level', '_department', '_specialty', '_group', 'start_date', 'end_date'], 'required', 'on' => self::SCENARIO_CHANGE_TYPE],
            [['number', 'summa', 'date', 'contract_form_type', '_contract_summa_type', '_contract_type', 'month_count', '_level'], 'required', 'on' => self::SCENARIO_CONTRACT_MANUAL],
            [['_contract_summa_type', '_contract_type', 'contract_form_type'], 'required', 'on' => self::SCENARIO_CREATE],
            [['date', 'updated_at', 'created_at', 'filename', 'discount', 'start_date', 'end_date'], 'safe'],
            [['summa', 'discount', 'different'], 'number'],
            [['_student', '_department', '_specialty', 'position'], 'default', 'value' => null],
            [['_student', '_specialty', 'position', '_student_contract_type', '_group', '_curriculum', 'month_count', 'education_period'], 'integer'],
            [['mailing_address', 'bank_details'], 'string'],
            [['active'], 'boolean'],
            [['number', '_contract_summa_type', 'contract_form_type', '_education_year', '_education_type', '_education_form', 'contract_status', '_contract_type', '_level', '_manual_type', '_graduate_type'], 'string', 'max' => 64],
            [['university_code'], 'string', 'max' => 10],
            [['rector', 'customer'], 'string', 'max' => 255],
            [['number'], 'unique'],
            [['_student_contract_type'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentContractType::className(), 'targetAttribute' => ['_student_contract_type' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_contract_summa_type'], 'exist', 'skipOnError' => true, 'targetClass' => ContractSummaType::className(), 'targetAttribute' => ['_contract_summa_type' => 'code']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_contract_type'], 'exist', 'skipOnError' => true, 'targetClass' => ContractType::className(), 'targetAttribute' => ['_contract_type' => 'code']],
            [['_level'], 'exist', 'skipOnError' => true, 'targetClass' => Course::className(), 'targetAttribute' => ['_level' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
                'number' => __('Number of Contract'),
                'start_date' => __('Contract start date'),
                'end_date' => __('Contract end date'),
                '_curriculum' => __('Curriculum Curriculum'),
            ]
        );
    }

    public function getStudentContractType()
    {
        return $this->hasOne(EStudentContractType::className(), ['id' => '_student_contract_type']);
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

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getContractSummaType()
    {
        return $this->hasOne(ContractSummaType::className(), ['code' => '_contract_summa_type']);
    }

    public function getContractType()
    {
        return $this->hasOne(ContractType::className(), ['code' => '_contract_type']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getFullName()
    {
        return strtoupper(trim($this->number . ' (' . Yii::$app->formatter->asDate($this->date->getTimestamp()) . ', ' . $this->summa . ')'));
    }

    public function getPaidContractFee()
    {
        return $this->hasMany(EPaidContractFee::class, ['_student_contract' => 'id']);
    }

    public function getContractInvoice()
    {
        return $this->hasMany(EStudentContractInvoice::class, ['_student_contract' => 'id']);
    }


    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += @$item[@$fieldName];
        }

        return @$total;
    }

    public function search($params, $asProvider = true)
    {
        $this->load($params);
        /*if ($this->_department == null) {
            $this->_specialty = null;
            $this->_education_form = null;
        }
        if ($this->_education_year == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student']);


        $defaultOrder = ['id' => SORT_ASC];
        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('number', $this->search);
            $query->orWhereLike('e_student.uzasbo_id_number', $this->search);
        }
        if ($this->_student_contract_type) {
            $query->andFilterWhere(['_student_contract_type' => $this->_student_contract_type]);
        }
        if ($this->_contract_summa_type) {
            $query->andFilterWhere(['_contract_summa_type' => $this->_contract_summa_type]);
        }
        if ($this->_contract_type) {
            $query->andFilterWhere(['_contract_type' => $this->_contract_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->contract_status) {
            $query->andFilterWhere(['contract_status' => $this->contract_status]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        $query->andFilterWhere(['_manual_type' => self::MANUAL_STATUS_TYPE_AUTO]);

//        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
//        $query->andFilterWhere(['e_student_meta.active' => StudentStatus::STATUS_ENABLE]);

        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
                            'id',
                            'number',
                            'summa',
                            '_student_contract_type',
                            '_contract_summa_type',
                            'contract_form_type',
                            '_contract_type',
                            'contract_status',
                            '_education_year',
                            '_student' => [
                                SORT_DESC => [
                                    'e_student.first_name' => SORT_DESC,
                                    'e_student.second_name' => SORT_DESC,
                                    'e_student.third_name' => SORT_DESC,
                                ],
                                SORT_ASC => [
                                    'e_student.first_name' => SORT_ASC,
                                    'e_student.second_name' => SORT_ASC,
                                    'e_student.third_name' => SORT_ASC,
                                ],
                            ],
                            '_specialty',
                            '_department',
                            '_curriculum',
                            '_group',
                            '_education_type',
                            '_education_form',
                            'university_code',
                            'rector',
                            'mailing_address',
                            'bank_details',
                            'contract_status',
                            'position',
                            'updated_at',
                            'created_at',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 100,
                    ],
                ]
            );
        } else {
            $query->addOrderBy($defaultOrder);
        }
        return $query;
    }

    public function search_payment($params, $asProvider = true, Admin $user = null)
    {
        $this->load($params);
        $query = self::find();
        $query->leftJoin('e_student', 'e_student.id=e_student_contract._student');
        $query->leftJoin('e_student_meta', 'e_student_meta._student=e_student_contract._student');
        $query->with(['student']);

        $defaultOrder = ['id' => SORT_ASC];

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('number', $this->search);
            $query->orWhereLike('e_student.uzasbo_id_number', $this->search);
        }
        if ($this->_student_contract_type) {
            $query->andFilterWhere(['_student_contract_type' => $this->_student_contract_type]);
        }
        if ($this->_contract_summa_type) {
            $query->andFilterWhere(['_contract_summa_type' => $this->_contract_summa_type]);
        }
        if ($this->_contract_type) {
            $query->andFilterWhere(['_contract_type' => $this->_contract_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_contract._education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['e_student_contract._department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_contract._group' => $this->_group]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_contract._education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_contract._education_form' => $this->_education_form]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_contract._education_year' => $this->_education_year]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['e_student_contract._level' => $this->_level]);
        }
        if ($user->role->isTutorRole()) {
            $query->andFilterWhere(['e_student_contract._group' => array_keys($user->tutorGroups)]);
        }

        $query->andFilterWhere(['contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED]);
        $query->andFilterWhere(['e_student_contract.active' => EStudentContract::STATUS_ENABLE]);
        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => StudentStatus::STATUS_ENABLE]);
        $query->andFilterWhere(['_manual_type' => self::MANUAL_STATUS_TYPE_AUTO]);

        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
                            'id',
                            'number',
                            'summa',
                            '_student_contract_type',
                            '_contract_summa_type',
                            'contract_form_type',
                            '_contract_type',
                            '_education_year',
                            '_student' => [
                                SORT_DESC => [
                                    'e_student.first_name' => SORT_DESC,
                                    'e_student.second_name' => SORT_DESC,
                                    'e_student.third_name' => SORT_DESC,
                                ],
                                SORT_ASC => [
                                    'e_student.first_name' => SORT_ASC,
                                    'e_student.second_name' => SORT_ASC,
                                    'e_student.third_name' => SORT_ASC,
                                ],
                            ],
                            '_specialty',
                            '_department',
                            '_curriculum',
                            '_group',
                            '_education_type',
                            '_education_form',
                            '_level',
                            'university_code',
                            'rector',
                            'mailing_address',
                            'bank_details',
                            'contract_status',
                            'position',
                            'updated_at',
                            'created_at',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 400,
                    ],
                ]
            );
        } else {
            $query->addOrderBy($defaultOrder);
        }
        return $query;
    }

    public function isBaseContractType()
    {
        return $this->_contract_type == ContractType::CONTRACT_TYPE_BASE;
    }

    public function isEducationFormDayly()
    {
        return $this->_education_form == EducationForm::EDUCATION_FORM_DAYLY;
    }

    public function isEducationFormSecondHigherDayly()
    {
        return $this->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_DAYLY;
    }

    public static function getMonthCountOptions()
    {
        $months = [];

        for ($i = 1; $i <= 12; $i++)
            $months [$i] = $i;

        return $months;
    }

    public function search_control($params)
    {
        $this->load($params);
        /*if ($this->_department == null) {
            $this->_specialty = null;
            $this->_education_form = null;
        }
        if ($this->_education_year == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'number',
                    'summa',
                    '_student_contract_type',
                    '_contract_summa_type',
                    'contract_form_type',
                    '_contract_type',
                    '_education_year',
                    '_student',
                    '_specialty',
                    '_department',
                    '_curriculum',
                    '_group',
                    '_level',
                    '_education_type',
                    '_education_form',
                    'university_code',
                    'rector',
                    'mailing_address',
                    'bank_details',
                    'contract_status',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }
        if ($this->_student_contract_type) {
            $query->andFilterWhere(['_student_contract_type' => $this->_student_contract_type]);
        }
        if ($this->_contract_summa_type) {
            $query->andFilterWhere(['_contract_summa_type' => $this->_contract_summa_type]);
        }
        if ($this->_contract_type) {
            $query->andFilterWhere(['_contract_type' => $this->_contract_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['_level' => $this->_level]);
        }
        $query->andFilterWhere(['contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED]);
        $query->andFilterWhere(['e_student_contract.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['_manual_type' => self::MANUAL_STATUS_TYPE_AUTO]);
        return $dataProvider;
    }

    public function search_manual($params)
    {
        $this->load($params);
        /*if ($this->_department == null) {
            $this->_specialty = null;
            $this->_education_form = null;
        }
        if ($this->_education_year == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'number',
                    'summa',
                    '_student_contract_type',
                    '_contract_summa_type',
                    'contract_form_type',
                    '_contract_type',
                    '_education_year',
                    '_student',
                    '_specialty',
                    '_department',
                    '_curriculum',
                    '_group',
                    '_education_type',
                    '_education_form',
                    'university_code',
                    'rector',
                    'mailing_address',
                    'bank_details',
                    'contract_status',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }
        if ($this->_student_contract_type) {
            $query->andFilterWhere(['_student_contract_type' => $this->_student_contract_type]);
        }
        if ($this->_contract_summa_type) {
            $query->andFilterWhere(['_contract_summa_type' => $this->_contract_summa_type]);
        }
        if ($this->_contract_type) {
            $query->andFilterWhere(['_contract_type' => $this->_contract_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        $query->andFilterWhere(['_manual_type' => self::MANUAL_STATUS_TYPE_MANUAL]);
        return $dataProvider;
    }

    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Contract Payment List'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('T/R'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Faculty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Level'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Student ID'), DataType::TYPE_STRING);

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Last Year Summa'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Current Year Summa'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Payment Detail'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Summa'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Debt'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Procent for Current Year'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Summa Type'), DataType::TYPE_STRING);
        $sheet->getStyle("A$row:O$row")->getFont()->setBold(true);
        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $row - 1,
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->getFullName(),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->group ? $model->group->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->department ? $model->department->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->specialty ? $model->specialty->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->level ? $model->level->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationForm ? $model->educationForm->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->uzasbo_id_number,
                DataType::TYPE_STRING
            );
            $indeptor = self::getInDebptor($model->_student, $model->_education_year, $model->id);
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                round($indeptor, 2),
                DataType::TYPE_NUMERIC
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->summa !== null ? round($model->summa, 2) : '0',
                DataType::TYPE_NUMERIC
            );
            $detail = "";

            foreach ($model->paidContractFee as $key => $item) {
                $detail .= $item->payment_number . ', ' . Yii::$app->formatter->asDate($item->payment_date) . ' - ' . Yii::$app->formatter->asCurrency($item->summa) . ' ' . PHP_EOL;
            }

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                substr($detail, 0, -2),
                DataType::TYPE_STRING2
            );
            $paid = EStudentContract::getTotal($model->paidContractFee, 'summa');
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $paid > 0 ? round($paid, 2) : '0',
                DataType::TYPE_NUMERIC
            );
            /*$sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->different !==null ?  round($model->different,2) : 0,
                DataType::TYPE_NUMERIC
            );*/
            $different = $model->summa - ($paid - $indeptor);
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $different !== null ? round($different, 2) : 0,
                DataType::TYPE_NUMERIC
            );
            $procent = $model->summa > 0 ? ($paid + $indeptor) * 100 / $model->summa : "";
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                round($procent, 1),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->contractSummaType ? $model->contractSummaType->name : '',
                DataType::TYPE_STRING
            );
        }
        foreach (range('A', 'O') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
            $sheet->getStyle('K')->getAlignment()->setWrapText(true);
        }

        $sheet->calculateColumnWidths();

        $name = 'Contract_payments-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public static function generateContractDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Contract List'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('T/R'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Faculty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty code'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Level'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport Pin'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Form Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Number of Contract'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Summa Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Summa'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Paid Summa'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Uzasbo Id Number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Discount'), DataType::TYPE_STRING);

        $sheet->getStyle("A$row:T$row")->getFont()->setBold(true);
        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $row - 1,
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->getFullName(),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationType ? $model->educationType->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationForm ? $model->educationForm->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->department ? $model->department->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->specialty ? $model->specialty->code : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->specialty ? $model->specialty->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->group ? $model->group->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->level ? $model->level->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->passport_number,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->passport_pin,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->contract_form_type ? EStudentContractType::getContractFormOptions()[$model->contract_form_type] : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->number,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                Yii::$app->formatter->asDate($model->date),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->contractType ? $model->contractType->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->contractSummaType ? $model->contractSummaType->name : '',
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->summa !== null ? round($model->summa, 2) : '0',
                DataType::TYPE_NUMERIC
            );
            $paid = EStudentContract::getTotal($model->paidContractFee, 'summa');
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $paid > 0 ? round($paid, 2) : '0',
                DataType::TYPE_NUMERIC
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->uzasbo_id_number,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->discount ? $model->discount : '',
                DataType::TYPE_STRING
            );
        }
        foreach (range('A', 'T') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
            // $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        }

        $sheet->calculateColumnWidths();

        $name = 'Contract_list-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public static function getInDebptor($student, $education_year, $contract)
    {
        $total = 0;
        $contracts = self::find()
            ->where(['_student' => $student, 'different_status' => self::DIFFERENT_NOT_DEBTOR_STATUS])
            ->andWhere(['<', '_education_year', $education_year])
            ->andWhere(['not', ['id' => $contract]])
            ->all();
        foreach ($contracts as $item) {
            $total += $item->different;
        }
        return -$total;
    }


    public function beforeSave($insert)
    {
        if ($this->hash == null) {
            $this->hash = gen_uuid();
        }

        return parent::beforeSave($insert);
    }

    public static function getEducationPeriodOptions()
    {
        $years = [];

        for ($i = date('Y'); $i < (date('Y') + 7); $i++)
            $years [$i] = $i;

        return $years;
    }

}
