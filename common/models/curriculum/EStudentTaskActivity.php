<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use frontend\models\curriculum\SubjectTask;
use frontend\models\curriculum\SubjectTaskStudent;
use frontend\models\system\Student;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "e_student_task_activity".
 *
 * @property int $id
 * @property int $_subject_task_student
 * @property int $_subject_task
 * @property int $_curriculum
 * @property int $_subject
 * @property int $_task_type
 * @property string $_training_type
 * @property string $_education_year
 * @property string $_semester
 * @property int $_student
 * @property int|null $_employee
 * @property string $send_date
 * @property string|null $filename
 * @property string $comment
 * @property int $attempt_count
 * @property float|null $mark
 * @property float|null $percent_b
 * @property float|null $percent_c
 * @property string|null $marked_date
 * @property string $marked_comment
 * @property int|null $position
 * @property int|null $correct
 * @property int|null $time
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property DateTime $started_at
 * @property DateTime $finished_at
 *
 * @property ECurriculum $curriculum
 * @property EEmployee $employee
 * @property EStudent $student
 * @property ESubject $subject
 * @property ESubjectTask $subjectTask
 * @property SubjectTaskStudent $subjectTaskStudent
 * @property HEducationYear $educationYear
 * @property HTrainingType $trainingType
 */
class EStudentTaskActivity extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE_FOR_STUDENT = 'create_for_student';
    const SCENARIO_CREATE_FOR_TEACHER = 'create_for_teacher';

    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    public $_questionsData = [];
    private $_questions = [];
    public $task_count ="";
    public static function tableName()
    {
        return 'e_student_task_activity';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getMarkBySubjectTask($subject_task_student = false)
    {
        return self::find()
            ->where([
                '_subject_task_student' => $subject_task_student,
                'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public static function getMarkBySubjectTaskStudent($subject_task = false, $student = false)
    {
        return self::find()
            ->where([
                '_subject_task' => $subject_task,
                '_student' => $student,
                //'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['>=', 'mark', 0])
            ->andWhere(['not', ['mark' => null]])
            ->orderBy(['marked_date' => SORT_DESC])
            ->one();
    }

    public static function getLastBySubjectTask($subject_task_student = false)
    {
        return self::find()
            ->where([
                '_subject_task_student' => $subject_task_student,
                //'active' => self::STATUS_ENABLE
            ])
            ->orderBy(['send_date' => SORT_DESC])
            ->one();
    }

    public static function getMarkByCurriculumSemesterSubject($student = false, $semester = false, $subject = false)
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
            //[['_subject_task_student', '_subject_task', '_curriculum', '_subject', '_training_type', '_education_year', '_semester', '_student', 'send_date', 'comment', 'attempt_count', 'marked_comment'], 'required'],
            [['comment', 'filename'], 'required', 'on' => self::SCENARIO_CREATE_FOR_STUDENT],
            [['mark', 'marked_comment'], 'required', 'on' => self::SCENARIO_CREATE_FOR_TEACHER],
            [['_subject_task_student', '_subject_task', '_curriculum', '_subject', '_student', '_employee', 'attempt_count', 'position'], 'default', 'value' => null],
            [['_subject_task_student', '_subject_task', '_curriculum', '_subject', '_student', '_employee', 'attempt_count', 'position', '_subject_topic', 'time'], 'integer'],
            [['send_date', 'filename', 'marked_date', '_translations', 'data'], 'safe'],
            [['comment', 'marked_comment'], 'string', 'max' => 5000],
            [['mark'], 'number'],
            [['_training_type', '_education_year', '_semester', '_task_type', 'status', '_final_exam_type'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_subject_task'], 'exist', 'skipOnError' => true, 'targetClass' => ESubjectTask::className(), 'targetAttribute' => ['_subject_task' => 'id']],
            [['_subject_task_student'], 'exist', 'skipOnError' => true, 'targetClass' => ESubjectTaskStudent::className(), 'targetAttribute' => ['_subject_task_student' => 'id']],
            [['_subject_topic'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumSubjectTopic::className(), 'targetAttribute' => ['_subject_topic' => 'id']],
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

    public function getSubjectTaskStudent()
    {
        return $this->hasOne(SubjectTaskStudent::className(), ['id' => '_subject_task_student']);
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

    public function getCurriculumSubjectTopic()
    {
        return $this->hasOne(ECurriculumSubjectTopic::className(), ['id' => '_subject_topic']);
    }

    public function getFinalExamType()
    {
        return $this->hasOne(FinalExamType::className(), ['code' => '_final_exam_type']);
    }

    public static function getActiveTopicData(SubjectTask $task, Student $student, $create = true)
    {
        $result = self::find()
            ->where(
                [
                    '_subject_task' => $task->_subject_task,
                    '_student' => $student->id,
                    'status' => self::STATUS_STARTED,
                ]
            )
            ->orderBy(
                [
                    'updated_at' => SORT_DESC,
                ]
            )
            ->one();

        $count = self::find()
            ->where(
                [
                    '_subject_task' => $task->_subject_task,
                    '_student' => $student->id,
                ]
            )
            ->orderBy(
                [
                    'updated_at' => SORT_DESC,
                ]
            )
            ->count();

        if ($result === null && $create) {
            if ($count >= $task->subjectTask->attempt_count) {
                return self::find()
                    ->where(
                        [
                            '_topic' => $task->_subject_task,
                            '_student' => $student->id,
                        ]
                    )
                    ->orderBy(
                        [
                            'updated_at' => SORT_DESC,
                        ]
                    )
                    ->one();
            }
            $data = [];
            $questions = $task->subjectTask->question_count;
            shuffle($questions);
            if ($task->subjectTask->question_count > 0) {
                $questions = array_slice($questions, 0, $task->subjectTask->test_questions);
            }

            foreach ($questions as $question) {
                $answers = array_keys($question->answers);
                shuffle($answers);

                $new = $answers;
                asort($new);
                $combine = array_combine($new, $answers);
                $data['q'][] = [
                    'id' => $question->id,
                    'b' => 0,
                    's' => [],
                    'm' => $combine,
                    'c' => $question->_answer,
                ];
            }

            $result = new self();
            $result->_student = $student->id;

            $result->_subject_task = $task->_subject_task;
            $result->_subject_task_student = $task->id;
            $result->_curriculum = $task->_curriculum;
            $result->_subject = $task->_subject;
            $result->_training_type = $task->_training_type;
            $result->_education_year = $task->_education_year;
            $result->_semester = $task->_semester;
            $result->send_date = date('Y-m-d H:i:s', time());
            $result->attempt_count = 1;
            $result->comment = "Test";

            $result->status = self::STATUS_STARTED;
            $result->started_at = time();
            $result->data = $data;

            $result->save(false);
        }

        return $result;
    }

    public function getActualTime()
    {
        return $this->started_at + ($this->subjectTask->test_duration) * 60 - time();
    }

    public function getQuestions()
    {
        if (empty($this->_questions)) {
            $questions = [];

            foreach ($this->data['q'] as $item) {
                $questions[$item['id']] = new ObjectID($item['id']);
                $this->_questionsData[$item['id']] = $item;
            }

            $qs = ESubjectResourceQuestion::find()
                ->where(['id' => array_values($questions)])
                ->all();

            foreach ($qs as $q) {
                $questions[$q->id] = $q;
            }

            foreach ($questions as $id => $q) {
                if (!($q instanceof Question)) {
                    unset($questions[$id]);
                }
            }

            $this->_questions = $questions;
        }

        return $this->_questions;
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['send_date' => SORT_DESC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_student',
                    '_employee',
                    '_semester',
                    '_education_year',
                    'code',
                    'position',
                    'send_date',
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

    public function searchForStudent(EStudent $student, ESubjectExamSchedule $schedule)
    {
        $trainings = array();
        if ($schedule->_exam_type == ExamType::EXAM_TYPE_MIDTERM || $schedule->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $schedule->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
            $trainings = array(TrainingType::TRAINING_TYPE_LECTURE => TrainingType::TRAINING_TYPE_LECTURE);
        } elseif ($schedule->_exam_type == ExamType::EXAM_TYPE_CURRENT || $schedule->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $schedule->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
            $trainings = array(TrainingType::TRAINING_TYPE_LABORATORY => TrainingType::TRAINING_TYPE_LABORATORY, TrainingType::TRAINING_TYPE_PRACTICE => TrainingType::TRAINING_TYPE_PRACTICE, TrainingType::TRAINING_TYPE_SEMINAR => TrainingType::TRAINING_TYPE_SEMINAR);
        }
        $lessons = ESubjectSchedule::getTeachersByCurriculumSemesterSubjectTraining($schedule->_curriculum, $schedule->_semester, $schedule->_subject, $trainings);
        $employees = array();
        foreach ($lessons as $item) {
            $employees [$item->_employee] = $item->_employee;
        }

    //    $query = self::find();
       // $this->load($params);
        $query = self::find()
            ->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task');
    /*        ->leftJoin('e_subject', 'e_subject.id=_subject');
/*            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['group', 'semester', 'group', 'subject', 'lessonPair', 'auditorium', 'employee']);
*/
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_student',
                    '_employee',
                    '_semester',
                    '_education_year',
                    'code',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
        $query->andFilterWhere([
            '_student' => $student->id,
            'e_student_task_activity._curriculum' => $schedule->_curriculum,
            'e_student_task_activity._semester' => $schedule->_semester,
            'e_student_task_activity._subject' => $schedule->_subject,
            'e_student_task_activity._education_year' => $schedule->_education_year,
            'e_subject_task._exam_type' => $schedule->_exam_type,
            'e_subject_task.active' => ESubjectTask::STATUS_ENABLE,
            'e_student_task_activity.active' => ESubjectTask::STATUS_ENABLE,
        ]);
        if($schedule->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST){
            $query->andFilterWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST]]);
        }
        elseif($schedule->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND){
            $query->andFilterWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
        }
        elseif($schedule->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD){
            $query->andFilterWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
        }
        $query->andWhere(['>', 'mark', 0]);
        $query->andWhere(['not', ['mark' => null]]);
        $query->andWhere(['in', 'e_student_task_activity._training_type', $trainings]);
        $query->andWhere(['in', 'e_student_task_activity._employee', $employees]);
        return $dataProvider;
    }
}
