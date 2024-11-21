<?php

namespace common\models\archive;

use common\components\Config;
use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeMeta;
use common\models\performance\EStudentPttCurriculumSubject;
use common\models\performance\EStudentPttSubject;
use common\models\structure\EDepartment;
use common\models\structure\EUniversity;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\Admin;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\TeacherPositionType;
use Yii;
use common\models\system\_BaseModel;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_academic_information".
 *
 * @property int $id
 * @property int $_specialty
 * @property int $_student_meta
 * @property int $_student
 * @property int $_department
 * @property int $_group
 * @property string|null $_education_year
 * @property string $_education_type
 * @property string $_education_form
 * @property string $_marking_system
 * @property int $_decree
 * @property string $academic_number
 * @property string $academic_register_number
 * @property \DateTime $academic_register_date
 * @property string|null $academic_status
 * @property string|null $filename
 * @property string|null $rector
 * @property string|null $dean
 * @property string|null $secretary
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EDecree $decree
 * @property EDepartment $department
 * @property EEducationYear $educationYear
 * @property EGroup $group
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EStudentMetum $studentMeta
 * @property HEducationForm $educationForm
 * @property HEducationType $educationType
 * @property HMarkingSystem $markingSystem
 */
class EAcademicInformation extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_INSERT_OTHER = 'insert_other';
    const SCENARIO_INSERT_CREDIT = 'insert_credit';

    const ACADEMIC_INFORMATION_STATUS_CREATE = '11';
    const ACADEMIC_INFORMATION_STATUS_PROCESS = '12';
    const ACADEMIC_INFORMATION_STATUS_GENERATED = '13';
    public $subjectIds = [];
    public $semester_id;
    protected $_translatedAttributes = [
        'university_name',
        'faculty_name',
        'student_name',
        'education_type_name',
        'education_form_name',
        'specialty_name',
        'rector',
        'dean',
        'student_status',
    ];

    public static function tableName()
    {
        return 'e_academic_information';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getContractStatusOptions()
    {
        return [
            self::ACADEMIC_INFORMATION_STATUS_CREATE => __('In the process of preparation academic information'), // Akademik ma'lumotnoma yaratish
            self::ACADEMIC_INFORMATION_STATUS_PROCESS => __('In the process of preparation academic information'), // Jarayonda
            self::ACADEMIC_INFORMATION_STATUS_GENERATED => __('The academic information is generated'), // Generatsiya qilingan
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['academic_register_number', 'academic_register_date', 'rector', 'dean', 'student_status', 'student_birthday', 'group_name', 'university_name', 'faculty_name', 'student_name', 'education_type_name', 'education_form_name', 'specialty_name', 'specialty_code', 'year_of_entered', 'year_of_graduated'], 'required', 'on'=> self::SCENARIO_INSERT],
            [['_marking_system', 'academic_number', 'academic_register_number', 'academic_register_date', 'rector', 'dean', 'secretary'], 'required', 'on'=> self::SCENARIO_INSERT_OTHER],
            [['academic_register_date'], 'required', 'on'=> self::SCENARIO_INSERT_CREDIT],
            [['_specialty', '_student_meta', '_student', '_department', '_group', '_decree', 'position'], 'default', 'value' => null],
            [['_specialty', '_student_meta', '_student', '_department', '_group', '_decree', 'position'], 'integer'],
            [['academic_register_date', 'filename', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['subjectIds', 'student_status'], 'safe'],
            [['_education_year', '_education_type', '_education_form', '_marking_system', 'academic_status'], 'string', 'max' => 64],
            [['_semester', 'semester_id'], 'string', 'max' => 64],
            [['academic_number'], 'string', 'max' => 20],
            [['academic_register_number'], 'string', 'max' => 30],
            [['rector', 'dean', 'secretary'], 'string', 'max' => 255],
            [['_student', '_student_meta'], 'unique', 'targetAttribute' => ['_student', '_student_meta']],
            [['_decree'], 'exist', 'skipOnError' => true, 'targetClass' => EDecree::className(), 'targetAttribute' => ['_decree' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_student_meta'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentMeta::className(), 'targetAttribute' => ['_student_meta' => 'id']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_marking_system'], 'exist', 'skipOnError' => true, 'targetClass' => MarkingSystem::className(), 'targetAttribute' => ['_marking_system' => 'code']],
            ['subjectIds', 'validateSubjectIds', 'message' => __('Subjects should be attached to the transcript')],
            ['academic_register_number', 'unique', 'message' => __('The transcript numbered {value} was created already')],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
                '_curriculum' => __('Curriculum Curriculum'),
                'student_status' => __('Student Status Label'),

            ]
        );
    }

    public function validateSubjectIds($attribute, $options)
    {
        if ($this->isNewRecord) {
            if (count($this->subjectIds) == 0) {
                $this->addError($attribute, $options['message']);
            }
        }
    }

    public function getDecree()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['id' => '_student_meta']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester', '_curriculum' => '_curriculum']);
    }

    /*public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['id' => '_semester']);
    }*/

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
       // $query->joinWith(['studentMeta']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                // 'defaultOrder' => ['e_subject.name' => SORT_ASC],
                'attributes' => [
                    '_student',
                    'academic_number',
                    'academic_register_number',
                    'academic_register_date',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_education_year) {
            $query->andWhere(['e_academic_information._education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andWhere(['e_academic_information._student' => $this->_student]);
        }
        return $dataProvider;
    }

    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        $query->joinWith(
            [
             //   'curriculum',
           //     'specialty',
         //       'group',
                'student'
     //           'department',
   //             'educationYear',
  //              'educationType',
//                'educationForm'
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

        /*if ($department) {
            $this->_department = $department;
        }*/

        if ($this->_department) {
            $query->andFilterWhere(['e_academic_information._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_academic_information._education_type' => $this->_education_type]);
        }

        if ($this->_specialty) {
            $query->andFilterWhere(['e_academic_information._specialty' => $this->_specialty]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_academic_information._education_form' => $this->_education_form]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_academic_information._group' => $this->_group]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_academic_information._curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'academic_register_date' => SORT_DESC,
                    ],
                    'attributes' => [
                        '_department',
                        '_curriculum',
                        'e_student.second_name',
                        'e_student.first_name',
                        'e_student.third_name',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_group',
                        '_specialty',
                        'created_at',
                        'academic_register_number',
                        'academic_register_date',
                        'subjects_count'
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }

    public function fillKeysFromStudent(EStudentMeta $student)
    {
        $this->_university = EUniversity::findCurrentUniversity()->id;
        $this->_specialty = $student->_specialty_id;
        $this->_curriculum = $student->_curriculum;
        //$this->_semester = Semester::getByCurriculumSemester($student->_curriculum, $student->_semestr)->code;
        $this->_student_meta = $student->id;
        $this->_student = $student->_student;
        $this->_department = $student->_department;
        $this->_group = $student->_group;
        $this->_education_year = $student->educationYear->educationYear ? $student->educationYear->educationYear->code : $student->educationYear->code;
        $this->_education_type = $student->educationType->code;
        $this->_education_form = $student->educationForm->code;
        $this->_marking_system = $student->curriculum ? $student->curriculum->_marking_system : '';
        $this->_decree = $student->decree ? $student->decree->id : $student->_decree;
        $this->rector = (EEmployeeMeta::getRectorName() != null)
            ? EEmployeeMeta::getRectorName()->employee->shortName : '';
        $this->dean = (EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN) != null)
            ? EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN)->employee->shortName : '';
        $this->academic_status = self::ACADEMIC_INFORMATION_STATUS_PROCESS;
    }

    public function fillFromStudent(EStudentMeta $student)
    {
        $lang = Config::getLanguageCode() === Config::LANGUAGE_ENGLISH_CODE ? Config::LANGUAGE_ENGLISH : Config::LANGUAGE_UZBEK;
        $this->student_name = $student->student->fullName;
        $this->student_birthday = $student->student->birth_date;
        $this->university_name = EUniversity::findCurrentUniversity()->getTranslation('name', $lang);
        $this->faculty_name = $student->department->getTranslation('name', $lang);
        $this->specialty_name = $student->specialty->mainSpecialty->getTranslation('name', $lang);
        $this->specialty_code = $student->specialty->code;
        $this->curriculum_name = $student->curriculum->getTranslation('name', $lang);;
        $this->rector = (EEmployeeMeta::getRectorName() != null)
            ? EEmployeeMeta::getRectorName()->employee->shortName : '';
        $this->dean = (EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN) != null)
            ? EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN)->employee->shortName : '';

        $this->education_form_name = $student->educationForm->getTranslation('name', $lang);
        $this->education_type_name = $student->educationType->getTranslation('name', $lang);
        //$this->education_period = $student->curriculum->education_period;
        $this->group_name = $student->group->name;
        $this->year_of_entered = $student->student->year_of_enter;
        $this->semester_name = Semester::getByCurriculumSemester($student->_curriculum, $student->_semestr)->getTranslation('name', $lang);
        if (Yii::$app->language === Config::LANGUAGE_ENGLISH) {
            $this->student_status = self::getStudentStatusEn();
        } else {
            $this->student_status = self::getStudentStatusUz();
        }
    }

    public function fillTranslationsFromStudent(EStudentMeta $student, $lang = Config::LANGUAGE_UZBEK)
    {
        $this->setTranslation(
            'specialty_name',
            $student->specialty->mainSpecialty->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation('faculty_name', $student->department->getTranslation('name', $lang, true), $lang);
        $this->setTranslation('group_name', $student->group->getTranslation('name', $lang, true), $lang);

        if ($lang === Config::LANGUAGE_UZBEK) {
            $fullName = strtoupper(
                trim(
                    $student->student->getTranslation('second_name', $lang, true)
                    . ' ' . $student->student->getTranslation('first_name', $lang, true)
                    . ' ' . $student->student->getTranslation('third_name', $lang, true)
                )
            );
        } else {
            $fullName = strtoupper(
                trim(
                    $student->student->getTranslation('second_name', $lang, true)
                    . ' ' . $student->student->getTranslation('first_name', $lang, true)
                )
            );
        }
        $this->setTranslation('student_name', $fullName, $lang);
        $this->setTranslation(
            'university_name',
            EUniversity::findCurrentUniversity()->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation(
            'education_type_name',
            $student->educationType->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation(
            'education_form_name',
            $student->educationForm->getTranslation('name', $lang, true),
            $lang
        );

        $rector = (EEmployeeMeta::getRectorName() != null) ? EEmployeeMeta::getRectorName()->employee : null;
        $rector_fullname = $rector ? trim(
            mb_substr($rector->getTranslation('first_name', $lang, true), 0, 1) . '.' . $rector->getTranslation(
                'second_name',
                $lang,
                true
            ),
            '.'
        ) : '';
        $this->setTranslation(
            'rector',
            $rector_fullname,
            $lang
        );

        $dean = (EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN) != null)
            ? EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN)->employee : null;
        $dean_fullname = $dean ? trim(
            mb_substr($dean->getTranslation('first_name', $lang, true), 0, 1) . '.' . $dean->getTranslation(
                'second_name',
                $lang,
                true
            ),
            '.'
        ) : '';
        $this->setTranslation(
            'dean',
            $dean_fullname,
            $lang
        );
        if ($lang === Config::LANGUAGE_ENGLISH) {
            $this->setTranslation(
                'student_status',
                self::getStudentStatusEn(),
                $lang
            );

        } else {
            $this->setTranslation(
                'student_status',
                self::getStudentStatusUz(),
                $lang
            );
        }
    }

    public static function getStudentStatusUz()
    {
        return 'Talaba';
    }

    public static function getStudentStatusEn()
    {
        return 'Student';
    }

    public static function getTranscriptLabelUz()
    {
        return 'AKADEMIK MA’LUMOTNOMA';
    }

    public static function getTranscriptLabelEn()
    {
        return 'Transcript of Academic Records';
    }

    public function getDepartmentItems()
    {
        return ArrayHelper::map(
            EDepartment::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => self::find()
                    ->select(['_department'])
                    ->distinct()
                    ->column()])
                ->all(), 'id', 'name');
    }


    public function getEducationTypeItems()
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_type'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getEducationFormItems()
    {
        return ArrayHelper::map(
            EducationForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_form'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getCurriculumItems()
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'id' => self::find()
                        ->select(['_curriculum'])
                        ->distinct()
                        ->column()])
                ->all(), 'id', 'name');
    }


    public function getGroupItems()
    {
        $query = EGroup::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_group'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function getSpecialtyItems()
    {
        $query = ESpecialty::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_specialty'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function getSemestersByEducationYear()
    {
        $data = [];

        foreach ($this->curriculum->semesters as $semester) {
            if (!isset($data[$semester->educationYear->name]))
                $data[$semester->educationYear->name] = [];

            $data[$semester->educationYear->name][$semester->code] = $semester->name;
        }

        return $data;
    }

    public function getStudentTranscriptSubjects()
    {
        return $this->hasMany(ETranscriptSubject::className(), ['_academic_information' => 'id'])
            //->with(['curriculumSubject'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public function getCurriculumSemesterSubjectsProvider()
    {
        $query = EStudentTranscriptCurriculumSubject::find()
            ->with(['subject', 'semester'])
            ->andFilterWhere([
                '_curriculum' => $this->_curriculum,
                'active' => true,
            ])
            ->andFilterWhere(['_semester' => Semester::find()
                ->select(['code'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    'active' => true
                ])
                ->andFilterWhere(['<=', 'code', $this->semester->code])
                ->column()
            ])
            ->orderBy([
                '_semester' => SORT_ASC,
                'position' => SORT_ASC,
            ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function getStudentTranscriptSubjectsProvider()
    {
        return new ActiveDataProvider(
            [
                'query' => $this
                    ->getStudentTranscriptSubjects()
                    ->with(['academicInformation']),
                'sort' => [
                    'attributes' => []
                ],
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function afterSave($insert, $changedAttributes)
    {
        /**
         * @var $subject EStudentPttCurriculumSubject
         * @var $user Admin
         */
        $user = \Yii::$app->user->identity;
        $graded = 0;
       // $lang = Config::getLanguageCode() === Config::LANGUAGE_ENGLISH_CODE ? Config::LANGUAGE_ENGLISH : Config::LANGUAGE_UZBEK;

        if ($this->subjectIds) {
            /*$subjects = EStudentTranscriptStudentSubject::find()
                ->with(['subject', 'semestr'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    '_student' => $this->_student,
                    'active' => true,
                    'id' => $this->subjectIds
                ])
                ->orderBy([
                    '_semester' => SORT_ASC,
                    'position' => SORT_ASC,
                ])
                ->all();

            $list = [];
            foreach ($subjects as $subj) {
                $list [$subj->_subject] = $subj->_subject;
            }
            $semesters = [];
            foreach ($subjects as $subj) {
                $semesters [$subj->_semester] = $subj->_semester;
            }
//            print_r($list);
            */$academic = EStudentTranscriptAcademicRecord::find()
                ->with(['subject'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    'active' => true,
                    '_student' => $this->_student,
                    'id' => $this->subjectIds
                    //'_subject' => $list
                ])
                //->andFilterWhere([ 'in', '_subject', $list])
                //->andFilterWhere([ 'in', '_semester', $semesters])
                ->orderBy([
                    '_semester' => SORT_ASC,
                    'position' => SORT_ASC,
                ])
                ->all();
            $data = [];
            $semester_name = null;
            foreach ($academic as $subject) {
                if(Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester) != null)
                    $semester_name = Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester)->name;
                elseif($subject->semester)
                    $semester_name = $data->$subject->name;
                else
                    $semester_name = \common\models\system\classifier\Semester::findOne($subject->_semester)->name;

                $data[] = [
                    '_academic_information' => $this->id,
                    '_student' => $subject->_student,
                    '_curriculum' => $subject->_curriculum,
                    '_education_year' => $subject->_education_year,
                    '_semester' => $subject->_semester,
                    '_subject' => $subject->_subject,
                    'curriculum_name' => $subject->curriculum_name,
                    'education_year_name' => $subject->education_year_name,
                    'semester_name' => $semester_name,
                    'student_name' => $subject->student_name,
                    'subject_name' => $subject->subject_name,
                    'total_acload' => $subject->total_acload,
                    'credit' => $subject->credit,
                    'total_point' => $subject->total_point,
                    'grade' => $subject->grade,
                ];
            }

            if (count($data))
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(ETranscriptSubject::tableName(), array_keys($data[0]), $data)
                    ->execute();
        }

        $this->updateAttributes([
            'subjects_count' => count($this->studentTranscriptSubjects),
        ]);



        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        if (!$this->canBeDeleted()) {
            throw new NotSupportedException(__('This transcript cannot be deleted'));
        }

        return parent::beforeDelete();
    }

    public function canBeUpdated()
    {
        return $this->student->meta->studentStatus->isStudyingStatus();
    }

    public function canBeDeleted()
    {
        return $this->getStudentTranscriptSubjects()
                ->orFilterWhere(['>', 'total_point', 0])
                ->orFilterWhere(['>', 'grade', 0])
                ->count() == 0;
    }

    public function getRegisterSubjectsProvider()
    {
        $query = EStudentTranscriptAcademicRecord::find()
            //->with(['subject'])
            ->andFilterWhere([
                '_curriculum' => $this->_curriculum,
                '_student' => $this->_student,
                'active' => true,
            ])
            ->andFilterWhere(['<=', '_semester', $this->_semester])
            /*->andFilterWhere(['_semester' => Semester::find()
                ->select(['code'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    'active' => true
                ])
                ->andFilterWhere(['<=', 'code', $this->_semester])
                ->column()
            ])*/
            ->orderBy([
                '_semester' => SORT_ASC,
                'position' => SORT_ASC,
            ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function getAcademicRecordSubjectsProvider()
    {
        $query = EStudentTranscriptAcademicRecord::find()
            //->with(['subject'])
            ->andFilterWhere([
                '_curriculum' => $this->_curriculum,
                '_student' => $this->_student,
                'active' => true,
            ])
            ->andFilterWhere(['<=', '_semester', $this->_semester])
            /*->andFilterWhere(['_semester' => Semester::find()
                ->select(['code'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    'active' => true
                ])
                ->andFilterWhere(['<=', 'code', $this->_semester])
                ->column()
            ])*/
            ->orderBy([
                '_semester' => SORT_ASC,
                'position' => SORT_ASC,
            ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function validateTranscriptTranslations()
    {
        $this->validate();
        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations ?: [];
            foreach ([Config::LANGUAGE_UZBEK, Config::LANGUAGE_ENGLISH] as $lang) {
                $langCode = Config::getShortLanguageCodes()[$lang];
                foreach ($this->_translatedAttributes as $attribute) {
                    if (!isset($translations[$attribute . '_' . $langCode]) || empty($translations[$attribute . '_' . $langCode])) {
                        $this->addError(
                            $attribute . '_' . $langCode,
                            __(
                                '{lang} translation for `{attribute}` can not be blank.',
                                [
                                    'lang' => Config::getLanguageLabel($lang),
                                    'attribute' => $this->getAttributeLabel($attribute)
                                ]
                            )
                        );
                    }
                }
            }
        }

        return !$this->hasErrors();
    }

    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);

        if (!in_array(Config::getLanguageCode(), [Config::LANGUAGE_UZBEK_CODE, Config::LANGUAGE_ENGLISH_CODE], true)) {
            foreach ($this->_translatedAttributes as $translatedAttribute) {
                $this->setTranslation($translatedAttribute, $this->$translatedAttribute, Config::LANGUAGE_UZBEK);
            }
        }
        //$this->validateTranslations();

        if ($this->hasErrors()) {
            return false;
        }



        return $result;
    }
}
