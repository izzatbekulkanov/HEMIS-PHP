<?php

namespace common\models\archive;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\performance\EPerformance;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * This is the model class for table "e_academic_record".
 *
 * @property int $id
 * @property int $_curriculum
 * @property string $_education_year
 * @property string $_semester
 * @property int $_student
 * @property int $_subject
 * @property string $curriculum_name
 * @property string $education_year_name
 * @property string $semester_name
 * @property string $student_name
 * @property string $student_id_number
 * @property string $subject_name
 * @property int|null $total_acload
 * @property int|null $credit
 * @property int $total_point
 * @property int $grade
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property int $_employee
 * @property string $employee_name
 *
 * @property EPerformance $performance
 * @property ECurriculum $curriculum
 * @property ECurriculumSubject $curriculumSubject
 * @property EStudent $student
 * @property Semester $semester
 * @property EGroup $group
 * @property ESubject $subject
 * @property EEmployee $employee
 * @property EducationYear $educationYear
 */
class EAcademicRecord extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    protected $_translatedAttributes = ['subject_name'];

    public $_group;

    public static function tableName()
    {
        return 'e_academic_record';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getAcadMarkByCurriculumSemesterSubject($student = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_student' => $student,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            // ->groupBy(['_employee'])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_curriculum', '_education_year', '_semester', '_student', '_subject', 'curriculum', 'education_year', 'semester', 'student', 'student_id_number', 'subject', 'total_point', 'grade', 'updated_at', 'created_at', '_employee', 'employee'], 'required'],
            [['_curriculum', '_student', '_subject', 'total_acload', 'credit', 'total_point', 'grade', 'position', '_employee'], 'default', 'value' => null],
            [['_curriculum', '_student', '_subject', 'position', '_employee', '_group'], 'integer'],
            [['active'], 'boolean'],
            [['total_acload', 'credit', 'total_point', 'grade'], 'required'],
            [['total_acload', 'credit', 'total_point', 'grade'], 'number'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_education_year', '_semester'], 'string', 'max' => 64],
            [['curriculum_name', 'education_year_name', 'semester_name', 'student_name', 'subject_name', 'employee_name'], 'string', 'max' => 256],
            [['student_id_number'], 'string', 'max' => 20],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_curriculum' => __('Curriculum Curriculum'),
                '_subject' => __('Subject'),
                'total_point' => __('Ball'),
                '_group' => __('Group'),
            ]
        );
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getPerformance()
    {
        return $this->hasOne(EPerformance::className(), [
            '_education_year' => '_education_year',
            '_semester' => '_semester',
            '_student' => '_student',
            '_subject' => '_subject',
            '_employee' => '_employee',
        ]);
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        $query->joinWith(['studentMeta', 'subject']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
               // 'defaultOrder' => ['e_subject.name' => SORT_ASC],
                'attributes' => [
                    '_student',
                    'diploma_number',
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

        /*if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }*/
        if ($this->_education_year) {
            $query->andWhere(['e_academic_record._education_year' => $this->_education_year]);
        }
        if ($this->_curriculum) {
            $query->andWhere(['e_academic_record._curriculum' => $this->_curriculum]);
        }
        if ($this->_semester) {
            $query->andWhere(['e_academic_record._semester' => $this->_semester]);
        }
        if ($this->_student) {
            $query->andWhere(['e_academic_record._student' => $this->_student]);
        }
        return $dataProvider;
    }


    public static function getStudentAcademicRecords(EStudent $student)
    {
        $result = [];
        foreach (self::find()
                     ->where(['_student' => $student->id])
                     ->orderBy(['semester_name' => SORT_ASC, 'subject_name' => SORT_ASC])
                     ->all() as $item) {
            $result = $item->getAcademicRecordData();
        }

        return $result;
    }

    public function getAcademicRecordData()
    {
        return [
            'semester' => [
                'id' => $this->_semester,
                'name' => $this->semester_name,
            ],
            'subject' => [
                'id' => $this->_subject,
                'name' => $this->subject_name,
            ],
            'curriculum' => [
                'id' => $this->_curriculum,
                'name' => $this->curriculum_name,
            ],
            'employee' => [
                'id' => $this->_employee,
                'name' => $this->employee_name,
            ],
            'education_year' => [
                'id' => $this->_education_year,
                'name' => $this->education_year_name,
            ],
            'total_acload' => $this->total_acload,
            'credit' => $this->credit,
            'total_point' => $this->total_point,
            'grade' => $this->grade,
            'position' => $this->position,
        ];
    }

    public function getCurriculumSubject()
    {
        return $this->hasOne(ECurriculumSubject::class, ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester']);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['_student' => '_student', '_education_year' => '_education_year', '_semestr' => '_semester']);
    }
}
