<?php

namespace common\models\student;

use common\components\db\PgQuery;
use common\models\academic\EDecree;
use common\models\archive\EAcademicInformation;
use common\models\archive\EAcademicRecord;
use common\models\archive\ECertificateCommitteeResult;
use common\models\archive\EStudentDiploma;
use common\models\archive\EStudentEmployment;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\finance\EStudentContract;
use common\models\performance\EPerformance;
use common\models\performance\EStudentGpa;
use common\models\structure\EDepartment;
use common\models\structure\EUniversity;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\SocialCategory;
use common\models\system\classifier\StipendRate;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\SubjectType;
use common\models\system\SystemLog;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "e_student_meta".
 *
 * @property int $id
 * @property string|null $student_id_number
 * @property int $_student
 * @property int $_decree
 * @property int $_specialty_id
 * @property int $_restore_meta_id
 * @property int|null $_department
 * @property string $_education_type
 * @property string $subjects_map
 * @property string|null $_education_form
 * @property string $_specialty
 * @property int|null $_curriculum
 * @property int|null $_semestr
 * @property int|null $_level
 * @property int|null $_group
 * @property int|null $_subgroup
 * @property string $_education_year
 * @property string $_payment_form
 * @property string $_student_status
 * @property int|null $position
 * @property bool|null $active
 * @property bool|null $accreditation_accepted
 * @property string|null $_translations
 * @property string $order_number
 * @property string $order_date
 * @property string $_status_change_reason
 *
 * @property ECurriculum $curriculum
 * @property EDepartment $department
 * @property EGroup $group
 * @property Course $level
 * @property EStudent $student
 * @property ESpecialty $specialty
 * @property EducationForm $educationForm
 * @property EducationYear $educationYear
 * @property EducationType $educationType
 * @property PaymentForm $paymentForm
 * @property Semester $semester
 * @property EDecree $decree
 * @property StudentStatus $studentStatus
 * @property EStudentGpa $studentGpa
 * @property MarkingSystem $markingSystem
 * @property EStudentDiploma $studentDiploma
 */
class EStudentMeta extends _BaseModel
{

    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_ORDER = 'order';
    const STATUS_REGISTRATION_ON = '1';
    const STATUS_REGISTRATION_OFF = 0;

    protected $_translatedAttributes = [];
    public $_students;
    public $gender;
    public $citizenship;
    public $nationality;
    public $province;
    public $_social_category;
    public $_contract_summa_type;
    public $_contract_type;
    public $contract_form_type;
    public $_subject;
    public $_stipend_rate;
    public $uzasbo_id_number;
    public $ip;
    public $message;

    public static function tableName()
    {
        return 'e_student_meta';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->active]) ? $labels[$this->active] : '';
    }

    public function getRegistrationLabel()
    {
        return self::getRegistrationOptions()[$this->diploma_registration ?? self::STATUS_REGISTRATION_OFF];
    }

    public function getEmploymentRegistrationLabel()
    {
        return self::getRegistrationOptions()[$this->employment_registration ?? self::STATUS_REGISTRATION_OFF];
    }

    public static function getRegistrationOptions()
    {
        return [
            self::STATUS_REGISTRATION_ON => __('Set'),
            self::STATUS_REGISTRATION_OFF => __('Not Set'),
        ];
    }

    public static function getLevelByEducationYear($education_year = false)
    {
        return self::find()
            ->where(
                [
                    '_education_year' => $education_year,
                    'active' => self::STATUS_ENABLE,
                ]
            )
            ->one()->level->name;
    }

    public static function getContingentByCurriculumSemester(
        $curriculum = false,
        $education_year = false,
        $semester = false
    )
    {
        return self::find()
            ->where(
                [
                    '_curriculum' => $curriculum,
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    'active' => self::STATUS_ENABLE,
                ]
            )
            // ->orderByTranslationField('name')
            ->all();
    }

    public static function getRealContingentByCurriculumSemester(
        $curriculum = false,
        $education_year = false,
        $semester = false
    )
    {
        return self::find()
            ->where(
                [
                    '_curriculum' => $curriculum,
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    //'active' => self::STATUS_ENABLE,
                ]
            )
            // ->orderByTranslationField('name')
            ->all();
    }

    public static function getContingentByYearSemesterGroup($education_year = false, $semester = false, $group = false)
    {
        return self::find()
            ->where(
                [
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    '_group' => $group,
                    'active' => self::STATUS_ENABLE,
                ]
            )
            ->all();
    }

    public static function getStudiedContingentByYearSemesterGroup($education_year = false, $semester = false, $group = false)
    {
        return self::find()
            ->where(
                [
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    '_group' => $group,
                    '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                    'active' => self::STATUS_ENABLE,
                ]
            )
            ->all();
    }

    public static function getStudiedContingentByYearSemester($education_year = false, $semester = false, $group = false)
    {
        return self::find()
            ->where(
                [
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    '_group' => $group,
                    '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                ]
            )
            ->all();
    }

    public static function getStudentByCurriculumYearSemesterActive(
        $curriculum = false,
        $education_year = false,
        $semester = false,
        $student = false,
        $active = false
    )
    {
        return self::find()
            ->where(
                [
                    '_curriculum' => $curriculum,
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    '_student' => $student,
                    'active' => $active,
                ]
            )
            ->one();
    }

    public static function getStudentByCurriculumYearSemester(
        $curriculum = false,
        $education_year = false,
        $semester = false,
        $student = false,
        $active = false
    )
    {
        return self::find()
            ->where(
                [
                    '_curriculum' => $curriculum,
                    '_education_year' => $education_year,
                    '_semestr' => $semester,
                    '_student' => $student,
                    //   'active' => $active,
                ]
            )
            ->one();
    }

    public function getSubjects()
    {
        return $this->hasMany(EStudentSubject::class, ['_student' => '_student', '_curriculum' => '_curriculum'])
            ->joinWith('curriculumSubject')
            ->where(['e_student_subject.active' => true, 'e_curriculum_subject.active' => true]);
    }

    public static function getStudentSubjects(EStudentMeta $studentMeta, $diff = false, $count = false, $orderBy = '_rating_grade, _semester')
    {
        $studentSubjects = $studentMeta->getSubjects()->select(
            ['e_student_subject._subject', 'e_student_subject._student', 'e_student_subject._curriculum', 'e_student_subject.active', 'e_curriculum_subject._curriculum', 'e_curriculum_subject._subject', 'e_curriculum_subject._semester', 'e_curriculum_subject.active']
        )->column();

        $curriculumSubjects = $studentMeta->curriculum->getSubjects()
            ->select(['_subject', '_curriculum', 'active', '_subject_type'])
            ->andFilterWhere(['_subject_type' => SubjectType::SUBJECT_TYPE_REQUIRED])->column();

        $query = $studentMeta->curriculum->getSubjects()
            ->andFilterWhere(
                ['_subject' => array_unique(array_merge($studentSubjects, $curriculumSubjects))]
            )
            ->orderBy($orderBy);
        if (is_array($diff)) {
            $query->andFilterWhere(
                ['not in', '_subject', $diff]
            );
        }
        if ($count == true) {
            $query = $query->count();
        }
        return $query;
    }

    public static function getMarkedSubjects(EStudentMeta $studentMeta, $count = false)
    {
        $studentSubjects = $studentMeta->getSubjects()->select(
            ['e_student_subject._subject', 'e_student_subject._student', 'e_student_subject._curriculum', 'e_student_subject.active', 'e_curriculum_subject._curriculum', 'e_curriculum_subject._subject', 'e_curriculum_subject._semester', 'e_curriculum_subject.active']
        )->column();
        $query = $studentMeta->getAcademicRecords()->select(['_student', '_subject', '_curriculum'])->andFilterWhere(
            ['_subject' => $studentSubjects, '_curriculum' => $studentMeta->_curriculum,/* '_semester' => $studentMeta->_semestr*/]
        )->distinct();
        if ($count == true) {
            $query = $query->count();
        }
        return $query;
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                //[['_student', '_education_type', '_specialty', '_education_year', '_payment_form', '_student_status', 'updated_at', 'created_at'], 'required'],
                [['_specialty_id', '_payment_form', '_department'], 'required', 'on' => self::SCENARIO_INSERT],
                [['_status_change_reason', '_decree',], 'required', 'on' => self::SCENARIO_ORDER],
                [
                    ['_specialty_id', '_education_year', '_payment_form', '_education_form', '_department'],
                    'required',
                    'on' => self::SCENARIO_UPDATE,
                ],
                [
                    [
                        '_student',
                        '_department',
                        '_curriculum',
                        '_semestr',
                        '_level',
                        '_group',
                        '_subgroup',
                        'position',
                        'employment_registration',
                        'diploma_registration',
                    ],
                    'default',
                    'value' => null,
                ],
                [
                    [
                        'id',
                        '_student',
                        '_department',
                        '_curriculum',
                        '_semestr',
                        '_level',
                        '_group',
                        '_subject',
                        '_subgroup',
                        'position',
                        'employment_registration',
                        'diploma_registration',
                        '_specialty_id',
                    ],
                    'integer',
                ],
                [['active'], 'boolean'],
                [['_translations', 'updated_at', 'created_at'], 'safe'],
                [['student_id_number'], 'string', 'max' => 14],
                [
                    [
                        '_status_change_reason',
                        '_education_type',
                        '_education_form',
                        '_education_year',
                        '_payment_form',
                        '_student_status',
                    ],
                    'string',
                    'max' => 64,
                ],
                [
                    ['_curriculum'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ECurriculum::className(),
                    'targetAttribute' => ['_curriculum' => 'id'],
                ],
                [
                    ['_department'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EDepartment::className(),
                    'targetAttribute' => ['_department' => 'id'],
                ],
                [
                    ['_group'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EGroup::className(),
                    'targetAttribute' => ['_group' => 'id'],
                ],
                [
                    ['_specialty_id'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESpecialty::className(),
                    'targetAttribute' => ['_specialty_id' => 'id'],
                ],
                [
                    ['_education_form'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationForm::className(),
                    'targetAttribute' => ['_education_form' => 'code'],
                ],
                [
                    ['_education_type'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationType::className(),
                    'targetAttribute' => ['_education_type' => 'code'],
                ],
                [
                    ['_payment_form'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => PaymentForm::className(),
                    'targetAttribute' => ['_payment_form' => 'code'],
                ],
                [
                    ['_decree'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EDecree::className(),
                    'targetAttribute' => ['_decree' => 'id'],
                ],
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_curriculum' => __('Curriculum Curriculum'),
                '_department' => __('Structure Faculty'),
                '_specialty_id' => __('Specialty'),
                '_student_status' => __('Transfer Status'),
                '_subject' => __('Subject'),
            ]
        );
    }

    /**
     * @return ECurriculum|\yii\db\ActiveQuery
     */
    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system'])
            ->viaTable('e_curriculum', ['id' => '_curriculum']);
    }

    public function getStudentGpa()
    {
        return $this->hasOne(EStudentGpa::className(), ['_education_year' => '_education_year', '_student' => '_student']);
    }

    public function getStudentDiploma()
    {
        return $this->hasOne(EStudentDiploma::class, ['_student' => '_student']);
    }

    public function getStudentAcademic()
    {
        return $this->hasOne(EAcademicInformation::class, ['_student_meta' => 'id']);
    }

    public function getOverallPerformances()
    {
        return $this->hasMany(EPerformance::class, ['_student' => 'id'])
            ->via('student')
            ->andWhere(['_exam_type' => ExamType::EXAM_TYPE_OVERALL]);
    }

    public function getAcademicRecords()
    {
        return $this->hasMany(EAcademicRecord::class, ['_student' => '_student', '_curriculum' => '_curriculum'])->where(['e_academic_record.active' => true]);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getDecree()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty_id']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semestr', '_curriculum' => '_curriculum']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getPaymentForm()
    {
        return $this->hasOne(PaymentForm::className(), ['code' => '_payment_form']);
    }

    public function getStudentStatus()
    {
        return $this->hasOne(StudentStatus::className(), ['code' => '_student_status']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
    }

    public function getCertificateCommitteeResult()
    {
        return $this->hasOne(ECertificateCommitteeResult::class, ['_student' => '_student']);
    }

    public function getStudentEmployment()
    {
        return $this->hasOne(EStudentEmployment::class, ['_student' => '_student']);
    }

    public function getSystemLog()
    {
        return $this->hasOne(SystemLog::class, ['_student' => '_student'])->orderBy(['e_system_log.created_at' => SORT_DESC]);
    }

    public function search($params)
    {
        $this->load($params);

        if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        if ($this->_semestr == null) {
            $this->_group = null;
        }

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['created_at' => SORT_DESC],
                    'defaultOrder' => ['e_student.second_name' => SORT_ASC],
                    'attributes' => [
                        //   'name',
                        'e_student.second_name',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        // $query->andFilterWhere(['>', 'e_student.id', 0]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        if ($this->employment_registration || $this->employment_registration === "0") {
            //if($this->employment_registration == self::STATUS_REGISTRATION_ON)
            $query->andFilterWhere(['employment_registration' => $this->employment_registration]);
            /*else{
                $query->andFilterWhere(['not', ['employment_registration' => null]]);
                $query->andFilterWhere(['not', ['employment_registration' => self::STATUS_REGISTRATION_ON]]);

            }*/
        }


        if ($this->diploma_registration) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }
        return $dataProvider;
    }

    public function searchAccredication($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['e_student.second_name' => SORT_ASC],
                    'attributes' => [
                        'e_student.second_name',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }

        if ($this->diploma_registration !== null) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }

        return $dataProvider;
    }

    public function searchDiploma($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['studentDiploma'], false);
        $query->orderBy('e_student_diploma.diploma_number');
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['e_student_diploma.diploma_number' => SORT_ASC/*, 'e_student.second_name' => SORT_ASC*/],
                    'attributes' => [
                        //'e_student.second_name',
                        'e_student_diploma.diploma_number',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );

        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_meta._education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['e_student_meta._payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['e_student_meta._specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['e_student_meta._semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['e_student_meta._student_status' => $this->_student_status]);
        }

        if ($this->diploma_registration !== null) {
            $query->andFilterWhere(['e_student_meta.diploma_registration' => $this->diploma_registration]);
        }

        if ($this->search) {
            $query->orWhereLike('e_student_diploma.diploma_number', $this->search);
            $query->orWhereLike('e_student_diploma.register_number', $this->search);
        }

        return $dataProvider;
    }

    public function searchForBatchRating($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['e_student.second_name' => SORT_ASC],
                    'attributes' => [
                        'e_student.second_name',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);

        return $dataProvider;
    }

    public function search_for_contract($params)
    {
        $this->load($params);

        /*if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        if ($this->_semestr == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        //   'name',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('e_student.uzasbo_id_number', $this->search);
        }

        // $query->andFilterWhere(['>', 'e_student.id', 0]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        if ($this->employment_registration) {
            $query->andFilterWhere(['employment_registration' => $this->employment_registration]);
        }
        if ($this->diploma_registration) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }
        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['_payment_form' => PaymentForm::PAYMENT_FORM_CONTRACT]);
        $query->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC]);
        return $dataProvider;
    }

    public function search_for_scholarship($params, StipendRate $stipend_rate = null)
    {
        $this->load($params);

        /*if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        if ($this->_semestr == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        //   'name',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        // $query->andFilterWhere(['>', 'e_student.id', 0]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        if ($this->employment_registration) {
            $query->andFilterWhere(['employment_registration' => $this->employment_registration]);
        }
        if ($this->diploma_registration) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }
        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => self::STATUS_ENABLE]);
        /*if ($stipend_rate->code == StipendRate::STIPEND_RATE_BASE) {
            $query->andFilterWhere(['_social_category' => SocialCategory::SOCIAL_CATEGORY_OTHER]);
        } elseif ($stipend_rate->code == StipendRate::STIPEND_RATE_INVALID) {
            $query->andFilterWhere(['_social_category' => SocialCategory::SOCIAL_CATEGORY_INVALID]);
        } elseif ($stipend_rate->code == StipendRate::STIPEND_RATE_ORPHANAGE) {
            $query->andFilterWhere(['in', '_social_category', [SocialCategory::SOCIAL_CATEGORY_ORPHANAGE, SocialCategory::SOCIAL_CATEGORY_PARENTAL_CARE]]);
        }*/
        /*elseif($stipend_rate->code == StipendRate::STIPEND_RATE_FAMOUS) {
            $query->andFilterWhere(['_social_category' => SocialCategory::SOCIAL_CATEGORY_OTHER]);
        }*/
        $query->andFilterWhere(['<>', '_citizenship', CitizenshipType::CITIZENSHIP_TYPE_FOREIGN]);
        //$query->andFilterWhere(['_social_category' => array_keys(SocialCategory::getBaseStatusOptions())]);
        //$query->andFilterWhere(['_payment_form' => PaymentForm::PAYMENT_FORM_CONTRACT]);
        $query->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC]);
        return $dataProvider;
    }

    public function calculateAcademicDebt()
    {
        $semester = (int)$this->_semestr - 1;
        $education_year = ((int)$this->_semestr % 2 == 0) ? $this->_education_year : (int)$this->_education_year - 1;
        $subjectIds = EStudentSubject::find()
            ->select([
                'e_curriculum_subject._semester',
                'e_curriculum_subject._subject',
                'e_curriculum_subject.total_acload',
                'e_curriculum_subject.credit',
                'e_academic_record.total_point',
                'e_academic_record.grade',
            ])
            ->leftJoin('e_curriculum_subject', '
            e_curriculum_subject._subject = e_student_subject._subject and 
            e_curriculum_subject._semester = e_student_subject._semester and 
            e_curriculum_subject._curriculum = e_student_subject._curriculum
            ')
            ->leftJoin('e_academic_record', '
            e_academic_record._student = e_student_subject._student and 
            e_academic_record._subject = e_student_subject._subject and 
            e_academic_record._semester = e_student_subject._semester and 
            e_academic_record._curriculum = e_student_subject._curriculum
            ')
            ->filterWhere([
                'e_student_subject._curriculum' => $this->_curriculum,
                'e_student_subject._student' => $this->_student,
                'e_student_subject._semester' => $semester,
                'e_student_subject._education_year' => $education_year,
            ])
            ->orderBy(['e_curriculum_subject._semester' => SORT_ASC])
            ->asArray()
            ->all();

        $subjects = 0;
        $minLimit = 0;
        $debts = 0;

        $excellent = 0;
        $fine = 0;
        $satisfactory = 0;
        $unsatisfactory = 0;
        $rating = 0;

        $marking_system = $this->curriculum->_marking_system;
        $five = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_FIVE);
        $four = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_FOUR);
        $three = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_THREE);
        $two = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_TWO);

        if ($this->curriculum->markingSystem->isCreditMarkingSystem()) {
            $minLimit = $this->curriculum->markingSystem->gpa_limit;
        } else if ($this->curriculum->markingSystem->isRatingSystem()) {
            $minLimit = intval(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_RATING, GradeType::GRADE_TYPE_THREE)->name);
        } else if ($this->curriculum->markingSystem->isFiveMarkSystem()) {
            $minLimit = round(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border, 0);
        }

        foreach ($subjectIds as $item) {
            //$totalCredits += intval($item['credit']);
            //$studentCredits += intval($item['credit']) * intval($item['grade']);
            $subjects += 1;
            if (intval($item['grade']) < $minLimit) {
                $debts++;
            }

            if (intval($item['grade']) >= $five->min_border) {
                $excellent++;
            } elseif (intval($item['grade']) >= $four->min_border) {
                $fine++;
            } elseif (intval($item['grade']) >= $three->min_border) {
                $satisfactory++;
            } else {
                $unsatisfactory++;
            }
        }
        if ($unsatisfactory > 0) {
            $rating = $two->name;
        } elseif ($satisfactory > 0) {
            $rating = $three->name;
        } elseif ($fine > 0) {
            $rating = $four->name;
        } elseif ($excellent > 0) {
            $rating = $five->name;
        }
        $satisfactory_amount = $subjects > 0 ? $satisfactory / $subjects * 100 : 0;
        $satisfactory_amount = $satisfactory_amount >= 30 ? __('Yes') : __('No');
        $data = [
            'data' => $subjectIds,
            'debt_subjects' => $debts,
            'subjects' => $subjects,
            'rating' => $rating,
            'satisfactory_amount' => $satisfactory_amount,
        ];
        return $data;
    }

    public function calculateContractDebt()
    {
        $education_year = ((int)$this->_semestr % 2 == 0) ? $this->_education_year : $this->_education_year - 1;

        $contractIds = EStudentContract::find()
            ->select([
                'id',
                'summa',
                '_education_year',
                '_student',
                '_curriculum',
            ])
            ->filterWhere([
                '_curriculum' => $this->_curriculum,
                '_student' => $this->_student,
                '_education_year' => $education_year,
            ])
            ->orderBy(['_education_year' => SORT_ASC])
            ->all();
        $summa = 0;
        $paid = 0;

        foreach ($contractIds as $item) {
            $summa = $item->summa;
            $paid = EStudentContract::getTotal($item->paidContractFee, 'summa');
        }
        $result = ($summa > 0) ? $paid / $summa : '0';
        $result = ($result >= ((int)$this->_semestr % 2 == 0 ? 1 : 0.5)) ? __('No') : __('Yes');

        $data = [
            'summa' => $summa,
            'paid' => $paid,
            'result' => $result,
        ];
        return $data;
    }

    public function searchForStudentStatus($params, Admin $admin, $faculty = null)
    {
        $this->load($params);
        $query = self::find();
        $query->joinWith(['student']);

        if ($faculty != null) {
            $this->_department = $faculty;
        }

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_student_status',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }

        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }

        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }

        if (count($admin->tutorGroups)) {
            $query->andFilterWhere(['_group' => array_keys($admin->tutorGroups)]);
        }

        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }

        $query->andFilterWhere(['_student_status' => array_keys(StudentStatus::getTransferStatusOptions())]);

        return $dataProvider;
    }

    public function searchForTransfer($params)
    {
        $this->load($params);

        if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        if ($this->_semestr == null) {
            $this->_group = null;
        }

        $query = self::find();
        $query->joinWith(['student']);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        'order_date',
                        'order_number',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('order_date', $this->search);
            $query->orWhereLike('order_number', $this->search);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }

        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => true]);

        return $dataProvider;
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();
        //->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);

        $query->joinWith(
            ['student', 'level', 'department', 'group', 'specialty', 'educationYear', 'educationType', 'educationForm']
        );

        $defaultOrder = ['_department' => SORT_ASC, '_specialty_id' => SORT_ASC, '_group' => SORT_ASC];

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            //$query->orWhereLike('h_course.name', $this->search);
        }

        if ($department) {
            $this->_department = $department;
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['_level' => $this->_level]);
        }

        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => true]);

        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
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
                            'position',
                            '_department',
                            '_specialty_id',
                            'e_student_meta._curriculum',
                            '_education_year',
                            '_education_type',
                            'e_student_meta._education_form',
                            '_payment_form',
                            '_semestr',
                            '_group',
                            'employment_registration',
                            'diploma_registration',
                            'updated_at',
                            'created_at',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]
            );
        } else {
            $query->addOrderBy($defaultOrder);
        }

        return $query;
    }

    public function generateAccreditationResult()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Student accreditation'));

        $row = 1;
        $col = 0;

        $sheet->setCellValueExplicitByColumnAndRow($col + 1, $row, $this->getAttributeLabel('_student'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 2, $row++, $this->student->getFullName(), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 1, $row, $this->getAttributeLabel('_specialty_id'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 2, $row++, $this->specialty->getFullName(), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 1, $row, $this->getAttributeLabel('_education_type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 2, $row++, $this->educationType->name, DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 1, $row, $this->getAttributeLabel('_education_form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 2, $row++, $this->educationForm->name, DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 1, $row, $this->getAttributeLabel('_curriculum'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 2, $row++, $this->curriculum->name, DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 1, $row, $this->getAttributeLabel('_marking_system'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col + 2, $row++, $this->markingSystem->name, DataType::TYPE_STRING);

        $col = 1;
        $row++;
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Curriculum subjects'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Studied subjects'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Semestr'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('O\'zlashtirgan'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Acload/credit'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Ball'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Grade'), DataType::TYPE_STRING);
        $sheet->getStyle("A$row:G$row")->getFont()->setBold(true);

        $row++;
        $helper = new Html();
        foreach (RatingGrade::getOptions() as $option => $ratingGrade) {
            $text = $helper->toRichTextObject("<strong>$ratingGrade</strong>");
            $sheet->mergeCells("A$row:G$row");
            $sheet->setCellValueExplicitByColumnAndRow(1, $row++, $text, DataType::TYPE_INLINE);
            $query = self::getStudentSubjects($this);
            $models = $query->andWhere(['_rating_grade' => $option])->all();
            ArrayHelper::multisort($models, 'subject.name');
            /** @var ECurriculumSubject $record */
            foreach ($models as $record) {
                $col = 1;
                $row++;
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, $record->subject->name, DataType::TYPE_STRING);
                if ($p = $record->getStudentSubjectRecord($this->_student)) {
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, $p->subject_name, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueExplicitByColumnAndRow(
                        $col++,
                        $row - 1,
                        $record->subject->name,
                        DataType::TYPE_STRING
                    );
                }
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, Semester::getByCurriculumSemester($record->_curriculum, $record->_semester)->name, DataType::TYPE_STRING);
                $studied = __('No');
                if ($p = $record->getStudentSubjectRecord($this->_student)) {
                    $studied = __('Yes');
                }
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, $studied, DataType::TYPE_STRING);
                $acload = $record->total_acload . ' / ' . $record->credit;
                if ($p = $record->getStudentSubjectRecord($this->_student)) {
                    $acload = $p->total_acload . ' / ' . $p->credit;
                }
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, $acload, DataType::TYPE_STRING);
                $ball = '-';
                if ($p = $record->getStudentSubjectRecord($this->_student)) {
                    $ball = $p->total_point;
                }
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, $ball, DataType::TYPE_STRING);
                $grade = '-';
                if ($p = $record->getStudentSubjectRecord($this->_student)) {
                    $grade = $p->grade;
                }
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row - 1, $grade, DataType::TYPE_STRING);
            }
        }

        foreach (range('A', 'G') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
        }

        $sheet->calculateColumnWidths();
        $name = 'Akkreditatsiya-' . $this->student->getFullName() . '-' . Yii::$app->formatter->asDatetime(
                time(),
                'php:d_m_Y_h_i_s'
            ) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Students'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Student ID'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport Pin'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Citizenship'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Country'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Nationality'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Province'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('District'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Gender'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Birth Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Level'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Faculty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Year'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Semester'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Payment Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Other'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Student Type'), DataType::TYPE_STRING);

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Decree'), DataType::TYPE_STRING);

        /**
         * @var $model self
         */
        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->student_id_number,
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
                $model->student->citizenship ? $model->student->citizenship->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->country ? $model->student->country->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->nationality ? $model->student->nationality->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->_country == 'UZ' && $model->student->province ? $model->student->province->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->_country == 'UZ' && $model->student->district ? $model->student->district->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->gender ? $model->student->gender->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->birth_date instanceof DateTime ? $model->student->birth_date->format(
                    'Y-m-d'
                ) : $model->student->birth_date,
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
                $model->department ? $model->department->name : '',
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
                $model->educationYear ? $model->educationYear->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->semester ? $model->semester->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->specialty && $model->specialty->mainSpecialty ? $model->specialty->mainSpecialty->code : '',
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
                $model->paymentForm ? $model->paymentForm->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->other,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->studentType ? $model->student->studentType->name : '',
                DataType::TYPE_STRING
            );
            if ($model->student->_decree_enroll){
                $sheet->setCellValueExplicitByColumnAndRow(
                    $col++,
                    $row,
                    @$model->student->decreeEnroll->getShortInformation(),
                    DataType::TYPE_STRING
                );
            }




        }

        $name = 'Talabalar-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public function canOperateGraduation()
    {
        return $this->accreditation_accepted && $this->_student_status == StudentStatus::STUDENT_TYPE_STUDIED;
    }

    public function canOperateGraduationSimple()
    {
        return $this->_student_status == StudentStatus::STUDENT_TYPE_STUDIED;
    }

    public function canOperateAcademicLeave()
    {
        return $this->_student_status == StudentStatus::STUDENT_TYPE_STUDIED;
    }

    public function canOperateExpel()
    {
        return $this->_student_status == StudentStatus::STUDENT_TYPE_STUDIED;
    }

    public function canOperateApplied()
    {
        if ($this->_student_status == StudentStatus::STUDENT_TYPE_APPLIED) {

            return $this->student->_sync_status == 'actual';

        }

        return false;
    }

    public function canOperateCourseTransfer()
    {
        if ($this->_student_status == StudentStatus::STUDENT_TYPE_STUDIED) {

            return $this->studentGpa != null;

            /*if ($this->markingSystem->isCreditMarkingSystem()) {
                return $this->studentGpa && $this->studentGpa->gpa >= $this->curriculum->markingSystem->gpa_limit;
            }

            return $this->studentGpa && $this->studentGpa->can_transfer;
            if ($this->markingSystem->isFiveMarkSystem()) {
                $min_border = round(GradeType::getGradeByCode($this->curriculum->_marking_system, GradeType::GRADE_TYPE_THREE)->min_border, 0);

                return EAcademicRecord::find()
                        ->where([
                            '_student' => $this->_student,
                            '_education_year' => $this->_education_year,
                        ])
                        ->andFilterWhere(['>=', 'grade', $min_border])
                        ->count() >= EStudentSubject::find()
                        ->where([
                            '_student' => $this->_student,
                            '_education_year' => $this->_education_year,
                        ])
                        ->count();
            }
            if ($this->markingSystem->isRatingSystem()) {
                return true;
            }*/
        }

        return false;
    }


    public function canOperateCourseExpel()
    {
        if ($this->_student_status == StudentStatus::STUDENT_TYPE_STUDIED) {
            return true;
        }

        return false;
    }

    public function canOperateRestore()
    {
        if (in_array($this->_student_status, array_keys(StudentStatus::getRestoreStatusOptions()))) {
            $student = EStudent::find()
                ->where([
                    'passport_pin' => $this->student->passport_pin
                ])
                ->leftJoin('e_student_meta', 'e_student.id=e_student_meta._student and e_student_meta.active=true')
                ->andWhere(new Expression('e_student_meta._student_status =:status'), [
                    'status' => StudentStatus::STUDENT_TYPE_STUDIED
                ])
                ->one();

            if ($student) return false;
            if ($this->semester == null) return false;
            if ($this->semester->level == null) return false;

            return true;
        }

        return false;
    }

    public function canOperateReturn()
    {
        if ($this->studentDiploma) {
            return !$this->studentDiploma->accepted && $this->_student_status == StudentStatus::STUDENT_TYPE_GRADUATED;
        } else {
            return $this->_student_status == StudentStatus::STUDENT_TYPE_GRADUATED;
        }
        return false;
    }

    public function getSubjectsWithAcademicRecord()
    {
        $subjectIds = EStudentSubject::find()
            ->select([
                'e_curriculum_subject._semester',
                'e_curriculum_subject._subject',
                'e_curriculum_subject._subject_type',
                'e_curriculum_subject.total_acload',
                'e_curriculum_subject.credit',
                'e_academic_record.id academic_record_id',
                'e_academic_record.total_point',
                'e_academic_record.grade',
            ])
            ->leftJoin('e_curriculum_subject', '
            e_curriculum_subject._subject = e_student_subject._subject and 
            e_curriculum_subject._semester = e_student_subject._semester and 
            e_curriculum_subject._curriculum = e_student_subject._curriculum
            ')
            ->leftJoin('e_academic_record', '
            e_academic_record._student = e_student_subject._student and 
            e_academic_record._subject = e_student_subject._subject and 
            e_academic_record._semester = e_student_subject._semester and 
            e_academic_record._curriculum = e_student_subject._curriculum
            ')
            ->filterWhere([
                'e_student_subject._curriculum' => $this->_curriculum,
                'e_student_subject._student' => $this->_student,
            ])
            ->andFilterWhere([
                '>', 'e_academic_record.grade', 0
            ])
            ->orderBy(['e_curriculum_subject._semester' => SORT_ASC, 'e_curriculum_subject.position' => SORT_ASC])
            ->asArray()
            ->all();

        $totalCredits = 0;
        $subjects = 0;
        $studentCredits = 0;
        $minLimit = 0;
        $debts = 0;


        if ($this->markingSystem->isCreditMarkingSystem()) {
            $minLimit = $this->markingSystem->gpa_limit;
        } else if ($this->markingSystem->isRatingSystem()) {
            $minLimit = intval(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_RATING, GradeType::GRADE_TYPE_THREE)->name);
        } else if ($this->markingSystem->isFiveMarkSystem()) {
            $minLimit = round(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border, 0);
        }

        foreach ($subjectIds as $item) {
            $totalCredits += intval($item['credit']);
            $studentCredits += intval($item['credit']) * intval($item['grade']);
            $subjects += 1;
            if (intval($item['grade']) < $minLimit) {
                $debts++;
            }
        }

        $data = [
            'data' => $subjectIds,
            'debt_subjects' => $debts,
            'subjects' => $subjects,
            'updated_at' => $this->getTimestampValue()
        ];

        if ($this->markingSystem->isCreditMarkingSystem()) {
            $data['gpa'] = $totalCredits > 0 ? round($studentCredits / $totalCredits, 1) : 0;
            $data['credit_sum'] = round($totalCredits, 1);
            $data['can_transfer'] = $data['gpa'] >= $this->markingSystem->gpa_limit;
        } else {
            $data['can_transfer'] = $debts == 0;
        }

        return $data;
    }

    public function searchForReturn($params)
    {
        $this->load($params);

        if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        if ($this->_semestr == null) {
            $this->_group = null;
        }

        $query = self::find();
        $query->joinWith(['student']);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        'order_date',
                        'order_number',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('order_date', $this->search);
            $query->orWhereLike('order_number', $this->search);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }

        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_GRADUATED]);

        return $dataProvider;
    }

    public function searchAcademic($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['studentAcademic'], false);
        $query->orderBy('e_academic_information.id');
//        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    // 'defaultOrder' => ['e_student.second_name' => SORT_ASC],
                    'attributes' => [
                        // 'e_student.second_name',
                        '_student',
                        'e_student_meta.id',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        '_education_year',
                        'e_student_meta._education_type',
                        'e_student_meta._education_form',
                        '_payment_form',
                        '_semestr',
                        'e_student_meta._group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }


        return $dataProvider;
    }

    public function search_uzasbo($params)
    {
        $this->load($params);

        if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        /*if ($this->_semestr == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student', 'group']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['created_at' => SORT_DESC],
                    'defaultOrder' => ['e_group.name' => SORT_ASC, 'e_student.second_name' => SORT_ASC],
                    'attributes' => [
                        //   'name',
                        'e_student.second_name',
                        '_student',
                        'e_group.name',

                        'e_student_meta.position',
                        'e_student_meta._department',
                        'e_student_meta._specialty_id',
                        'e_student_meta._curriculum',
                        '_education_year',
                        '_education_type',
                        'e_student_meta._education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'e_student.uzasbo_id_number',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('e_student.uzasbo_id_number', $this->search);
        }

        //$query->andFilterWhere(['>', 'e_student.id', 0]);
        if ($this->uzasbo_id_number) {
            $query->andFilterWhere(['e_student.uzasbo_id_number' => $this->uzasbo_id_number]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['e_student_meta._specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        if ($this->employment_registration) {
            $query->andFilterWhere(['employment_registration' => $this->employment_registration]);
        }
        if ($this->diploma_registration) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }
        $query->andFilterWhere(['e_student_meta.active' => true]);
        return $dataProvider;
    }

    public function search_fixed($params)
    {
        $this->load($params);

        /*if ($this->_curriculum == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_semestr = null;
        }
        if ($this->_semestr == null) {
            $this->_group = null;
        }*/

        $query = self::find();
        $query->joinWith(['student']);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['created_at' => SORT_DESC],
                    'defaultOrder' => ['e_student.second_name' => SORT_ASC],
                    'attributes' => [
                        //   'name',
                        'e_student.second_name',
                        '_student',
                        'position',
                        '_department',
                        '_specialty_id',
                        '_curriculum',
                        //'_education_year',
                        '_education_type',
                        '_education_form',
                        '_payment_form',
                        '_semestr',
                        '_group',
                        'employment_registration',
                        'diploma_registration',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        // $query->andFilterWhere(['>', 'e_student.id', 0]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        /*if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }*/
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        if ($this->employment_registration) {
            $query->andFilterWhere(['employment_registration' => $this->employment_registration]);
        }
        if ($this->diploma_registration) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }
        return $dataProvider;
    }

    public static function generateContingentDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Student Contingents'));

        $row = 1;
        $col = 1;

        //$sheet->setActiveSheetIndex(0)->mergeCells('A1:A3');

        $sheet->mergeCells("A1:A2");
        $sheet->setCellValueExplicitByColumnAndRow(1, $row, __('Student ID'), DataType::TYPE_STRING);
        $sheet->mergeCells("B$row:E$row");
        $sheet->setCellValueExplicitByColumnAndRow(2, $row, __('Passport Information'), DataType::TYPE_STRING);

        $sheet->mergeCells("F1:F2");
        $sheet->setCellValueExplicitByColumnAndRow(6, $row, __('Birth Date'), DataType::TYPE_STRING);
        $sheet->mergeCells("G1:G2");
        $sheet->setCellValueExplicitByColumnAndRow(7, $row, __('Phone number (99 123 45 67)'), DataType::TYPE_STRING);
        $sheet->mergeCells("H1:H2");
        $sheet->setCellValueExplicitByColumnAndRow(8, $row, __('Name of HEI'), DataType::TYPE_STRING);
        $sheet->mergeCells("I1:I2");
        $sheet->setCellValueExplicitByColumnAndRow(9, $row, __('Bachelor/Master'), DataType::TYPE_STRING);
        $sheet->mergeCells("J1:J2");
        $sheet->setCellValueExplicitByColumnAndRow(10, $row, __('Education form'), DataType::TYPE_STRING);

        $sheet->mergeCells("K1:K2");
        $sheet->setCellValueExplicitByColumnAndRow(11, $row, __('Specialty code'), DataType::TYPE_STRING);
        $sheet->mergeCells("L1:L2");
        $sheet->setCellValueExplicitByColumnAndRow(12, $row, __('Specialty'), DataType::TYPE_STRING);

        $sheet->mergeCells("M1:M2");
        $sheet->setCellValueExplicitByColumnAndRow(13, $row, __('Student Level'), DataType::TYPE_STRING);
        $sheet->mergeCells("N1:N2");
        $sheet->setCellValueExplicitByColumnAndRow(14, $row, __('Group'), DataType::TYPE_STRING);


        $sheet->mergeCells("O$row:R$row");
        $sheet->setCellValueExplicitByColumnAndRow(15, $row, __('Permanent residence address'), DataType::TYPE_STRING);
        $sheet->mergeCells("S$row:U$row");
        //  $col = 16;
        $sheet->setCellValueExplicitByColumnAndRow(19, $row, __('Temporary residence address'), DataType::TYPE_STRING);

        $sheet->mergeCells("V1:V2");
        $sheet->setCellValueExplicitByColumnAndRow(22, $row, __('Student Accommodation'), DataType::TYPE_STRING);

        $sheet->mergeCells("W1:W2");
        $sheet->setCellValueExplicitByColumnAndRow(23, $row, __('Faculty'), DataType::TYPE_STRING);

        $sheet->getStyle("A$row:W$row")->getFont()->setBold(true);

        $sheet->getStyle("A$row:W$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row:W$row")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $col = 2;
        $row++;
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Citizenship'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport Pin'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport data'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name of student'), DataType::TYPE_STRING);

        $col = 15;
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Country'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Province'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('District'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Permamant address'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Province'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('District'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Temporary address'), DataType::TYPE_STRING);
        $sheet->getStyle("A$row:W$row")->getFont()->setBold(true);
        //$sheet->getStyle("A$row:Q$row")->getAlignment()->setShrinkToFit(true);
        $sheet->getStyle("A$row:W$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);;
        /**
         * @var $model self
         */
        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->student_id_number,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->citizenship ? $model->student->citizenship->name : '',
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
                $model->student->passport_number,
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
                $model->student->birth_date instanceof DateTime ? $model->student->birth_date->format(
                    'Y-m-d'
                ) : $model->student->birth_date,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->phone,
                DataType::TYPE_STRING
            );
            $univer = EUniversity::findCurrentUniversity();
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $univer ? $univer->name : '',
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
                $model->specialty && $model->specialty->mainSpecialty ? $model->specialty->mainSpecialty->code : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->specialty && $model->specialty->mainSpecialty ? $model->specialty->mainSpecialty->name : '',
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
                $model->group ? $model->group->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->country ? $model->student->country->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->_country == 'UZ' && $model->student->province ? $model->student->province->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->_country == 'UZ' && $model->student->district ? $model->student->district->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->home_address,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->_country == 'UZ' && $model->student->currentProvince ? $model->student->currentProvince->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->_country == 'UZ' && $model->student->currentDistrict ? $model->student->currentDistrict->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->current_address,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->accommodation ? $model->student->accommodation->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->department ? $model->department->name : '',
                DataType::TYPE_STRING
            );
        }

        foreach (range('A', 'W') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
            //$sheet->getStyle('J')->getAlignment()->setWrapText(true);
        }
        $sheet->calculateColumnWidths();

        $name = 'Talabalar_kontingenti-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public function search_employment($params, $asProvider = true)
    {
        $this->load($params);
        $query = self::find();
        $query->joinWith(['student']);
        $defaultOrder = ['e_student.second_name' => SORT_ASC];
        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        // $query->andFilterWhere(['>', 'e_student.id', 0]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_student_status) {
            $query->andFilterWhere(['_student_status' => $this->_student_status]);
        }
        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_GRADUATED]);
        if ($this->employment_registration || $this->employment_registration === "0") {
            //if($this->employment_registration == self::STATUS_REGISTRATION_ON)
            $query->andFilterWhere(['employment_registration' => $this->employment_registration]);
            /*else{
                $query->andFilterWhere(['not', ['employment_registration' => null]]);
                $query->andFilterWhere(['not', ['employment_registration' => self::STATUS_REGISTRATION_ON]]);

            }*/
        }


        if ($this->diploma_registration) {
            $query->andFilterWhere(['diploma_registration' => $this->diploma_registration]);
        }

        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
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
                            'e_student.second_name',
                            'position',
                            '_department',
                            '_specialty_id',
                            '_curriculum',
                            '_education_year',
                            '_education_type',
                            '_education_form',
                            '_payment_form',
                            '_semestr',
                            '_group',
                            'employment_registration',
                            'diploma_registration',
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

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForLog($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $subQuery = SystemLog::find()
            ->select(['_student', 'MAX(created_at) AS created_at'])
            ->groupBy('_student');

        $query = self::find();
        $query->leftJoin('e_system_log', 'e_system_log._student=e_student_meta._student');
        $query->innerJoin(['l' => $subQuery], 'e_system_log._student = l._student AND e_system_log.created_at = l.created_at');

        $query->select([
            'e_student_meta._student',
            'e_student.first_name',
            'e_student.second_name',
            'e_student.third_name',
            'e_student_meta._department',
            'e_student_meta._specialty_id',
            'e_student_meta._education_type',
            'e_student_meta._education_form',
            'e_student_meta._payment_form',
            'e_student_meta._group',
            'l.created_at',
            'e_system_log.ip',
            'e_system_log.message',
        ]);

        $query->joinWith(
            ['student',  /*'department', 'group', 'specialty', 'educationType', 'educationForm'*/]
        );

        $defaultOrder = ['created_at' => SORT_DESC, '_department' => SORT_ASC, '_specialty_id' => SORT_ASC, 'e_student_meta._group' => SORT_ASC];

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            //$query->orWhereLike('h_course.name', $this->search);
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }

        if ($department) {
            $query->andFilterWhere(['e_student_meta._department' => $department]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['_semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['_level' => $this->_level]);
        }

        $query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => true]);


        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
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
                            'position',
                            '_department',
                            '_specialty_id',
                            'e_student_meta._curriculum',
                            '_education_year',
                            'e_student_meta._education_type',
                            'e_student_meta._education_form',
                            '_payment_form',
                            'e_student_meta._group',
                            'created_at',
                            'e_system_log.ip',
                            'e_system_log.message',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 20,
                    ],
                ]
            );
        } else {
            $query->addOrderBy($defaultOrder);
        }

        return $query;
    }

    public static function shortTitle($message = false)
    {
        $title = StringHelper::truncateWords($message, 12);

        if (strlen($title) > 120) {
            return StringHelper::truncate($title, 120);
        }
        return $title;
    }
}
