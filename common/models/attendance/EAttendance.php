<?php

namespace common\models\attendance;

use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\LessonPair;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;

use common\models\system\classifier\EducationType;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Imagine\Image\ManipulatorInterface;

/**
 * This is the model class for table "e_attendance".
 *
 * @property int $id
 * @property int $_subject_schedule
 * @property int $_student
 * @property string $_education_year
 * @property string $_semester
 * @property int $_subject
 * @property string $_training_type
 * @property string $_lesson_pair
 * @property DateTime $lesson_date
 * @property int $_employee
 * @property int|null $absent_on
 * @property int|null $absent_off
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EGroup $group
 * @property Semester $semester
 * @property EEmployee $employee
 * @property EStudent $student
 * @property ESubject $subject
 * @property LessonPair $lessonPair
 * @property ESubjectSchedule $subjectSchedule
 * @property EducationYear $educationYear
 * @property TrainingType $trainingType
 * @property EAttendanceActivity $attendanceActivity
 */
class EAttendance extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const ATTENDANCE_ABSENT_ON = '11';
    const ATTENDANCE_ABSENT_OFF = '12';
    const SCENARIO_INSERT = 'register';
    const SCENARIO_CHANGE_DEAN = 'change_dean';


    protected $_translatedAttributes = ['name'];
    public $summary;
    public $_department;
    public $start_date;
    public $end_date;

    public static function tableName()
    {
        return 'e_attendance';
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'start_date' => __('Start Date'),
            'end_date' => __('End Date'),
            '_group' => __('Group'),
            'selectedStudents' => __('Selected Students'),
        ]);
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getValueOptions()
    {
        return [
            self::ATTENDANCE_ABSENT_ON => __('Absent On'),
            self::ATTENDANCE_ABSENT_OFF => __('Absent Off'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_department', 'start_date', 'end_date'], 'safe'],
            [['_subject_schedule', '_student', '_subject', '_employee', 'absent_on', 'absent_off'], 'default', 'value' => null],
            [['_subject_schedule', '_student', '_subject', '_employee', 'absent_on', 'absent_off'], 'integer'],
            [['lesson_date', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_year', '_semester', '_training_type', '_lesson_pair'], 'string', 'max' => 64],
            [['_student', '_semester', '_subject', '_training_type', '_lesson_pair', 'lesson_date'], 'unique', 'targetAttribute' => ['_student', '_semester', '_subject', '_training_type', '_lesson_pair', 'lesson_date']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_subject_schedule'], 'exist', 'skipOnError' => true, 'targetClass' => ESubjectSchedule::className(), 'targetAttribute' => ['_subject_schedule' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
        ]);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student'])->with(['meta']);
    }

    public function getAttendanceActivity()
    {
        return $this->hasOne(EAttendanceActivity::className(), ['_attendance' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['_student' => '_student', '_education_year' => '_education_year', '_semestr' => '_semester']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getLessonPair()
    {
        return $this->hasOne(LessonPair::className(), ['code' => '_lesson_pair']);
    }

    public function getSubjectSchedule()
    {
        return $this->hasOne(ESubjectSchedule::className(), ['id' => '_subject_schedule']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
            ->viaTable('e_employee_meta', ['_employee' => 'id'])->with(['structureType']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group'])
            ->viaTable('e_subject_schedule', ['id' => '_subject_schedule'])->with(['department']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_education_year',
                    '_semester',
                    'lesson_date',
                    '_subject',
                    '_group',
                    '_training_type',
                    '_lesson_pair',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        return $dataProvider;
    }

    public function searchForActivity($params = [], $faculty = null)
    {
        $this->load($params);

        if ($faculty) {
            $this->_department = $faculty;
        }

        if ($this->_education_year == null) {
            $this->_education_year = EducationYear::getCurrentYear()->code;
        }

        $query = self::find()->with([
            'subjectSchedule',
            'educationYear',
            'subject',
            'student',
            'employee',
            'lessonPair',
            'semester',
            'attendanceActivity',
        ]);

        $query->leftJoin('e_student', 'e_student.id = e_attendance._student');
        $query->leftJoin('e_employee', 'e_employee.id = e_attendance._employee');
        $query->leftJoin('e_subject_schedule', 'e_subject_schedule.id = e_attendance._subject_schedule');
        $query->leftJoin('e_group', 'e_group.id = e_subject_schedule._group');
        $query->leftJoin('e_subject', 'e_subject.id = e_attendance._subject');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['lesson_date' => SORT_DESC, '_lesson_pair' => SORT_ASC],
                'attributes' => [
                    '_education_year',
                    '_semester',
                    'lesson_date',
                    '_subject',
                    '_group',
                    '_training_type',
                    '_lesson_pair',
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
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);

            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);

            $query->orWhereLike('e_group.name', $this->search);
            $query->orWhereLikeTranslation('name', $this->search, 'e_subject._translations');
        }

        if ($this->start_date) {
            if ($date = date_create_from_format('Y-m-d', $this->start_date)) {
                $query->andFilterWhere(['>=', 'e_attendance.lesson_date', $date->format('Y-m-d H:i')]);
            }
        }

        if ($this->end_date) {
            if ($date = date_create_from_format('Y-m-d', $this->end_date)) {
                $query->andFilterWhere(['<=', 'e_attendance.lesson_date', $date->format('Y-m-d H:i')]);
            }
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_group._department' => $this->_department]);
        }

        return $dataProvider;
    }


    public function getDepartmentItems()
    {
        $items = ArrayHelper::map(
            EDepartment::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'id' => EGroup::find()
                        ->select(['_department'])
                        ->filterWhere(['active' => true])
                        ->distinct()
                        ->column()
                ])
                ->all(), 'id', 'name');

        if (!isset($items[$this->_department]))
            $this->_department = null;

        return $items;
    }

    public function canChangeAttendance()
    {
        if($this->student->meta){
            if ($this->student->meta->semester->code == $this->semester->code) {
                return true;
            }
        }

        return false;
    }
}
