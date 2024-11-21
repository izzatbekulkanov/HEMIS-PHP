<?php

namespace common\models\archive;

use common\components\Config;
use common\models\academic\EDecree;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeMeta;
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
 * This is the model class for table "e_academic_information_data".
 *
 * @property int $id
 * @property int $_specialty
 * @property int $_student_meta
 * @property int $_student
 * @property int|null $_group
 * @property string|null $_semester
 * @property int|null $_university
 * @property int $_department
 * @property int|null $_decree
 * @property string|null $_education_year
 * @property string|null $_education_type
 * @property string|null $_education_form
 * @property int|null $_curriculum
 * @property string|null $first_name
 * @property string|null $second_name
 * @property string|null $third_name
 * @property string|null $student_birthday
 * @property string|null $group_name
 * @property string|null $blank_number
 * @property string|null $register_number
 * @property \DateTime|null $register_date
 * @property string|null $given_city
 * @property string|null $semester_name
 * @property string|null $university_name
 * @property string|null $rector_fullname
 * @property string|null $dean_fullname
 * @property string|null $secretary_fullname
 * @property string|null $faculty_name
 * @property string|null $continue_start_date
 * @property string|null $continue_end_date
 * @property string|null $moved_hei_name
 * @property string|null $studied_start_date
 * @property string|null $studied_end_date
 * @property string|null $specialty_name
 * @property string|null $specialty_code
 * @property float|null $accumulated_points
 * @property float|null $passing_score
 * @property string|null $last_education
 * @property string|null $expulsion_decree_reason
 * @property string|null $expulsion_decree_number
 * @property string|null $expulsion_decree_date
 * @property string|null $education_form_name
 * @property string|null $academic_data_status
 * @property int|null $subjects_count
 * @property string|null $_translations
 * @property string|null $filename
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EDecree $decree
 * @property EDepartment $department
 * @property EEducationYear $educationYear
 * @property EGroup $group
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EStudentMeta $studentMeta
 * @property EUniversity $university
 * @property HEducationForm $educationForm
 * @property HEducationType $educationType
 * @property EAcademicInformationDataSubject[] $eAcademicInformationDataSubjects
 */
class EAcademicInformationData extends _BaseModel
{
    /**
     * {@inheritdoc}
     */
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'insert';
    const ACADEMIC_INFORMATION_DATA_STATUS_CREATE = '11';
    const ACADEMIC_INFORMATION_DATA_STATUS_PROCESS = '12';
    const ACADEMIC_INFORMATION_DATA_STATUS_GENERATED = '13';


    public $subjectIds = [];
    public $semester_id;
    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return 'e_academic_information_data';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                [
                'first_name',
                'second_name',
                'third_name',
                'student_birthday',
                'group_name',
                'university_name',
                'rector_fullname',
                'dean_fullname',
                'secretary_fullname',
                'faculty_name',
                'specialty_name',
                'specialty_code',
                'last_education',
                'given_city',
                '_decree',
                'accumulated_points',
                'passing_score',
                'blank_number',
                'register_number',
                'register_date',
                'expulsion_decree_reason',
                'continue_start_date',
                 'continue_end_date',
                 'education_form_name',

                ],
                    'required',
                    'on'=> self::SCENARIO_INSERT
            ],
            [['_specialty', '_student_meta', '_student', '_group', '_university', '_department', '_decree', '_curriculum', 'subjects_count', 'position'], 'default', 'value' => null],
            [['_specialty', '_student_meta', '_student', '_group', '_university', '_department', '_decree', '_curriculum', 'subjects_count', 'position'], 'integer'],
            [['student_birthday', 'register_date', 'continue_start_date', 'continue_end_date', 'studied_start_date', 'studied_end_date', 'expulsion_decree_date', '_translations', 'filename', 'updated_at', 'created_at'], 'safe'],
            [['accumulated_points', 'passing_score'], 'number'],
            [['active'], 'boolean'],
            [['_semester', '_education_year', '_education_type', '_education_form', 'academic_data_status', 'semester_id'], 'string', 'max' => 64],
            [['first_name', 'second_name', 'third_name', 'group_name', 'specialty_code'], 'string', 'max' => 100],
            [['blank_number'], 'string', 'max' => 20],
            [['register_number'], 'string', 'max' => 30],
            [['given_city', 'semester_name', 'university_name', 'rector_fullname', 'dean_fullname', 'secretary_fullname', 'faculty_name', 'specialty_name', 'last_education', 'expulsion_decree_reason', 'expulsion_decree_number', 'education_form_name', 'education_form_name_moved'], 'string', 'max' => 255],
            [['moved_hei_name'], 'string', 'max' => 1000],
            [['blank_number'], 'unique'],
            [['register_number'], 'unique'],
            [['_student', '_student_meta'], 'unique', 'targetAttribute' => ['_student', '_student_meta']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_decree'], 'exist', 'skipOnError' => true, 'targetClass' => EDecree::className(), 'targetAttribute' => ['_decree' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_student_meta'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentMeta::className(), 'targetAttribute' => ['_student_meta' => 'id']],
            [['_university'], 'exist', 'skipOnError' => true, 'targetClass' => EUniversity::className(), 'targetAttribute' => ['_university' => 'id']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            ['subjectIds', 'validateSubjectIds', 'message' => __('Subjects should be attached to the academic information data')],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
                '_curriculum' => __('Curriculum Curriculum'),
                '_decree' => __('Academic Information Decree'),
                'education_form_name' => __('Academic Information Data Education Form Name'),

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

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
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

    public function getUniversity()
    {
        return $this->hasOne(EUniversity::className(), ['id' => '_university']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEAcademicInformationDataSubjects()
    {
        return $this->hasMany(EAcademicInformationDataSubject::className(), ['_academic_information_data' => 'id']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester', '_curriculum' => '_curriculum']);
    }

    public function getStudentAcademicInformationDataSubjects()
    {
        return $this->hasMany(EAcademicInformationDataSubject::className(), ['_academic_information_data' => 'id'])
            //->with(['curriculumSubject'])
            ->orderBy(['id' => SORT_ASC]);
    }

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
                    'blank_number',
                    'register_number',
                    'register_date',
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
            $query->andWhere(['e_academic_information_data._education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andWhere(['e_academic_information_data._student' => $this->_student]);
        }
        return $dataProvider;
    }

    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        $query->joinWith(
            [
                'student'
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
            $query->andFilterWhere(['e_academic_information_data._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_academic_information_data._education_type' => $this->_education_type]);
        }

        if ($this->_specialty) {
            $query->andFilterWhere(['e_academic_information_data._specialty' => $this->_specialty]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_academic_information_data._education_form' => $this->_education_form]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_academic_information_data._group' => $this->_group]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_academic_information_data._curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'register_number' => SORT_DESC,
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
                        'blank_number',
                        'register_number',
                        'register_date',
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
        $this->_decree = $student->decree ? $student->decree->id : $student->_decree;
        $this->rector_fullname = (EEmployeeMeta::getRectorName() != null)
            ? EEmployeeMeta::getRectorName()->employee->shortName : '';
        $this->dean_fullname = (EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN) != null)
            ? EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN)->employee->shortName : '';
        $this->academic_data_status = self::ACADEMIC_INFORMATION_DATA_STATUS_PROCESS;
    }

    public function fillFromStudent(EStudentMeta $student)
    {
        $lang = Config::getLanguageCode() === Config::LANGUAGE_ENGLISH_CODE ? Config::LANGUAGE_ENGLISH : Config::LANGUAGE_UZBEK;
        $this->first_name = $student->student->first_name;
        $this->second_name = $student->student->second_name;
        $this->third_name = $student->student->third_name;
        $this->student_birthday = $student->student->birth_date;
        $this->university_name = EUniversity::findCurrentUniversity()->getTranslation('name', $lang);
        $this->faculty_name = $student->department->getTranslation('name', $lang);
        $this->specialty_name = $student->specialty->mainSpecialty->getTranslation('name', $lang);
        $this->specialty_code = $student->specialty->code;
        $this->education_form_name = $student->educationForm->name;
        $this->last_education = $student->student->getTranslation('other', $lang);
        $this->given_city = EUniversity::findCurrentUniversity()->getTranslation('address', $lang);
        $this->rector_fullname = (EEmployeeMeta::getRectorName() != null)
            ? EEmployeeMeta::getRectorName()->employee->shortName : '';
        $this->dean_fullname = (EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN) != null)
            ? EEmployeeMeta::getLeaderName($student->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN)->employee->shortName : '';

       // $this->education_form_name = $student->educationForm->getTranslation('name', $lang);
       // $this->education_type_name = $student->educationType->getTranslation('name', $lang);
        //$this->education_period = $student->curriculum->education_period;
        $this->group_name = $student->group->name;
        $this->semester_name = Semester::getByCurriculumSemester($student->_curriculum, $student->_semestr)->getTranslation('name', $lang);

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

    public function getRegisterSubjectsProvider()
    {
        $query = EStudentAcademicInformationDataAcademicRecord::find()
            ->andFilterWhere([
                '_curriculum' => $this->_curriculum,
                '_student' => $this->_student,
                'active' => true,
            ])
            ->andFilterWhere(['<=', '_semester', $this->_semester])
            ->orderBy([
                '_semester' => SORT_ASC,
                'subject_name' => SORT_ASC,
 //               'position' => SORT_ASC,
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

    public function getStudentAcademicInformationDataSubjectsProvider()
    {
        return new ActiveDataProvider(
            [
                'query' => $this
                    ->getStudentAcademicInformationDataSubjects()
                    ->with(['academicInformationData']),
                'sort' => [
                    'attributes' => [
                        '_semester' => SORT_ASC,
                        'subject_name' => SORT_ASC,
                    ]

                ],
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function afterSave($insert, $changedAttributes)
    {
        $user = \Yii::$app->user->identity;
        $graded = 0;
        if ($this->subjectIds) {
           $academic = EStudentAcademicInformationDataAcademicRecord::find()
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
                    '_academic_information_data' => $this->id,
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
                    ->batchInsert(EAcademicInformationDataSubject::tableName(), array_keys($data[0]), $data)
                    ->execute();
        }

        $this->updateAttributes([
            'subjects_count' => count($this->studentAcademicInformationDataSubjects),
        ]);



        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        if (!$this->canBeDeleted()) {
            throw new NotSupportedException(__('This academic information data cannot be deleted'));
        }

        return parent::beforeDelete();
    }

    public function canBeUpdated()
    {
        return $this->student->meta->studentStatus->isExpelStatus();
    }

    public function canBeDeleted()
    {
        return $this->getStudentAcademicInformationDataSubjects()
                ->orFilterWhere(['>', 'total_point', 0])
                ->orFilterWhere(['>', 'grade', 0])
                ->count() == 0;
    }

    public static function normalizeStringLines($strings, $length, $lines)
    {
        $i = 0;
        $firstLine = '';
        foreach ($strings as $string) {
            if (strlen($firstLine .= ' ' . $string) < $length + 1) {
                $i++;
            } else {
                break;
            }
        }
        $result = [];
        $result[] = implode(' ', array_slice($strings, 0, $i));
        if ($lines >= 3) {
            $result[] = implode(' ', array_slice($strings, $i, $i));
            $result[] = implode(' ', array_slice($strings, $i * 2));
        } else {
            $result[] = implode(' ', array_slice($strings, $i));
        }
        return $result;
    }


}
