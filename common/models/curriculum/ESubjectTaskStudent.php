<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use frontend\models\curriculum\SubjectTaskActivity;
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

/**
 * This is the model class for table "e_subject_task_student".
 *
 * @property int $id
 * @property int $_subject_task
 * @property int $_subject_resource
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_training_type
 * @property string $_education_year
 * @property string $_semester
 * @property int $_employee
 * @property int $_task_type
 * @property int $_student
 * @property int $_group
 * @property int $percent
 * @property int $correct
 * @property int $attempt_count
 * @property string[] $data
 * @property DateTime $finished_at
 * @property DateTime $started_at
 * @property string $_task_status
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property string|null $published_at
 *
 * @property ECurriculum $curriculum
 * @property EEmployee $employee
 * @property EGroup $group
 * @property EStudent $student
 * @property ESubject $subject
 * @property ESubjectTask $subjectTask
 * @property ESubjectResource $subjectResource
 * @property EducationYear $educationYear
 * @property TrainingType $trainingType
 * @property EStudentTaskActivity taskStudentActivity
 */
class ESubjectTaskStudent extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_EXIST = 'create_final_exam';
    const SCENARIO_DELETE = 'delete';
    const TASK_STATUS_GIVEN = 11;
    const TASK_STATUS_PASSED = 12;
    const TASK_STATUS_RATED = 13;
    const TEST_STATUS_STARTED = 'started';
    const TEST_STATUS_FINISHED = 'finished';
    //final_active =1; default active
    public $mark;
    //public $_task_status;

    public static function tableName()
    {
        return 'e_subject_task_student';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }


    public function getTaskStatusLabel()
    {
        $items = self::getTaskStatusOptions();
        return isset($items[$this->_task_status]) ? $items[$this->_task_status] : $this->_task_status;
    }

    public static function getTaskStatusOptions()
    {
        return [
            self::TASK_STATUS_GIVEN => __('Task has been given'), // Talabaga topshiriq berildi
            self::TASK_STATUS_PASSED => __('Task has been passed'), // Talaba topshirdi
            self::TASK_STATUS_RATED => __('Task has been rated'), // Talaba baholandi
        ];
    }

    public static function getStudentsByTaskCurriculumSubject($subject_task = false, $curriculum = false, $subject = false)
    {
        return self::find()
            ->where([
                '_subject_task' => $subject_task,
                '_curriculum' => $curriculum,
                '_subject' => $subject,
            ])
            ->all();
    }

    public static function getExistStudentsByTaskCurriculumSubject($subject_task = false, $curriculum = false, $subject = false)
    {
        return self::find()
            ->where([
                '_subject_task' => $subject_task,
                '_curriculum' => $curriculum,
                '_subject' => $subject,
            ])
            ->count();
    }

    public static function getStudentBySubjectTask($student = false, $subject_task = false, $curriculum = false, $subject = false, $final_exam_type = false )
    {
        return self::find()
            ->where([
                '_student' => $student,
                '_subject_task' => $subject_task,
                '_curriculum' => $curriculum,
                '_subject' => $subject,
                '_final_exam_type' => $final_exam_type,
            ])
            ->one();
    }


    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_subject_task', '_curriculum', '_subject', '_training_type', '_education_year', '_semester', '_employee', '_student', '_group', '_task_status', '_task_type'], 'safe', 'on' => self::SCENARIO_CREATE],
            [['final_active'], 'safe', 'on' => self::SCENARIO_CREATE_EXIST],
            [['_subject_task', '_curriculum', '_subject', '_employee', '_student', '_group', 'position'], 'default', 'value' => null],
            [['_subject_task', '_curriculum', '_subject', '_employee', '_student', '_group', 'position', 'attempt_count', 'final_active'], 'integer'],
            [['active'], 'boolean'],
            [['deadline', 'active'], 'safe'],
            [['_final_exam_type', '_task_status'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_subject_task'], 'exist', 'skipOnError' => true, 'targetClass' => ESubjectTask::className(), 'targetAttribute' => ['_subject_task' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
            [['_final_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => FinalExamType::className(), 'targetAttribute' => ['_final_exam_type' => 'code']],

        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getSubjectTask()
    {
        return $this->hasOne(ESubjectTask::className(), ['id' => '_subject_task']);
    }

    public function getSubjectResource()
    {
        return $this->hasOne(ESubjectResource::className(), ['id' => '_subject_resource']);
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

    public function getTaskStudentActivity()
    {
        return $this->hasOne(SubjectTaskActivity::className(), ['_subject_task_student' => 'id', '_student' => '_student']);
    }

    public function getAllTaskStudentActivity()
    {
        return $this->hasMany(SubjectTaskActivity::className(), ['_subject_task' => '_subject_task', '_student' => '_student']);
    }


    public function getFinalExamType()
    {
        return $this->hasOne(FinalExamType::className(), ['code' => '_final_exam_type']);
    }

    public function beforeSave($insert)
    {
        if (is_string($this->deadline)) {
            if ($date = date_create_from_format('Y-m-d H:i', $this->deadline, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->deadline = $date;
            }
        }

        return parent::beforeSave($insert);
    }

   /*public function afterSave($insert, $changedAttributes)
    {
        //if ($this->final_active) {
            $current = self::getStudentBySubjectTask($this->_student, $this->_subject_task, $this->_curriculum, $this->_subject, $this->_final_exam_type);
            if ($current && $this->_final_exam_type != $current->_final_exam_type) {
                $current->updateAttributes(['final_active' => 0]);
            }
        //}

        parent::afterSave($insert, $changedAttributes);
    }*/

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
                    '_curriculum',
                    '_subject',
                    '_student',
                    '_employee',
                    '_semester',
                    '_education_year',
                    '_task_status',
                    'active',
                    'code',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            //$query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        return $dataProvider;
    }

    public function search_by_task($params)
    {
        $this->load($params);
        $query = self::find()
                ->leftJoin('e_student', 'e_student.id=e_subject_task_student._student')
                ->leftJoin('e_student_meta', 'e_student_meta._student=e_subject_task_student._student AND e_student_meta._semestr=e_subject_task_student._semester')
                ->with(['student']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [

                    'e_student.second_name' => SORT_ASC
                ],
                'attributes' => [
                    '_student' => [
                        SORT_ASC => ['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC],
                    ],
                    'e_subject_task_student.id',
                    'e_subject_task_student._curriculum',
                    '_subject',
                    '_student',
                    'e_student.second_name',
                    '_employee',
                    '_semester',
                    'e_subject_task_student._education_year',
                    'e_subject_task_student.active',
                    'e_student_meta._group',
                    '_task_status',
                    '_final_exam_type',
                    'code',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            //$query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_task_student._education_year' => $this->_education_year]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }
        if ($this->_task_status) {
            $query->andFilterWhere(['_task_status' => $this->_task_status]);
        }
        if ($this->_final_exam_type) {
            $query->andFilterWhere(['_final_exam_type' => $this->_final_exam_type]);
        }

        return $dataProvider;
    }

    public function getStatusClass()
    {
        return $this->_task_status == self::TASK_STATUS_GIVEN ? "btn-danger" : (($this->_task_status == self::TASK_STATUS_PASSED) ? "btn-info" : "btn-success");
    }

    public function getTitle()
    {
        return $this->subjectTask ? $this->subject->name . ' / ' . $this->subjectTask->name : ($this->subjectResource ? $this->subjectResource->subjectTopic->name : '');
    }

    public function canStartTest()
    {
        if ($this->subjectResource) {
            return $this->subjectResource->canStartTest();
        } elseif ($this->subjectTask) {
           // return $this->subjectTask->canStartTest() && ($this->attempt_count < $this->subjectTask->attempt_count);
            return $this->canSubmitTask() && ($this->subjectTask->getTestQuestions()->where(['active' => true])->count() > 0)  && ($this->attempt_count < $this->subjectTask->attempt_count);
        }
        return true;
    }

    public function canSubmitTask()
    {
        return time() < $this->deadline->getTimestamp();
    }
}
