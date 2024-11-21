<?php

namespace common\models\attendance;

use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\LessonPair;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\classifier\TrainingType;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_student_grade".
 *
 * @property int $id
 * @property int $_subject_schedule
 * @property int $_student
 * @property string $_education_year
 * @property string $_semester
 * @property int $_subject
 * @property string $_training_type
 * @property string $_lesson_pair
 * @property string|\DateTime $lesson_date
 * @property int $_employee
 * @property int|null $grade
 * @property int|null $absent_off
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEmployee $employee
 * @property EStudent $student
 * @property ESubject $subject
 * @property ECurriculumSubjectTopic $subjectTopic
 * @property LessonPair $lessonPair
 * @property ESubjectSchedule $subjectSchedule
 * @property EducationYear $educationYear
 * @property TrainingType $trainingType
 */
class EStudentGrade extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    protected $_translatedAttributes = ['name'];
    public $summary;
    public $grade_count;

    public static function tableName()
    {
        return 'e_student_grade';
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
            //[['_subject_schedule', '_student', '_education_year', '_semester', '_subject', '_training_type', '_lesson_pair', 'lesson_date', '_employee', 'updated_at', 'created_at'], 'required'],
            [['_subject_schedule', '_student', '_subject', '_employee', '_subject_topic', 'grade'], 'default', 'value' => null],
            [['_subject_schedule', '_student', '_subject', '_employee', '_subject_topic', 'grade'], 'integer'],
            [['lesson_date', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_year', '_semester', '_training_type', '_lesson_pair'], 'string', 'max' => 64],
            [['_student', '_subject_schedule'], 'unique', 'targetAttribute' => ['_student', '_subject_schedule']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_subject_topic'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumSubjectTopic::className(), 'targetAttribute' => ['_subject_topic' => 'id']],
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
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['_student' => '_student', '_education_year' => '_education_year', '_semestr' => '_semester']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getSubjectTopic()
    {
        return $this->hasOne(ECurriculumSubjectTopic::className(), ['id' => '_subject_topic']);
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
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        return $dataProvider;
    }
}
