<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use frontend\models\curriculum\SubjectResource;
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
 * This is the model class for table "e_subject_task".
 *
 * @property int $id
 * @property string $name
 * @property string $comment
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_language
 * @property string $_training_type
 * @property int|null $_subject_topic
 * @property string $_education_year
 * @property string $_semester
 * @property string $_task_type
 * @property int $_employee
 * @property string $_marking_category
 * @property int $max_ball
 * @property string $deadline
 * @property int|null $attempt_count
 * @property int|null $question_count
 * @property int|null $test_duration
 * @property string|null $filename
 * @property int|null $position
 * @property bool|null $active
 * @property bool|null $random
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property string|null $published_at
 *
 * @property ECurriculum $curriculum
 * @property ECurriculumSubjectTopic $subjectTopic
 * @property EEmployee $employee
 * @property ESubject $subject
 * @property EducationYear $educationYear
 * @property Language $language
 * @property TrainingType $trainingType
 * @property ESubjectResourceQuestion[] $testQuestions
 * @property ESubjectResourceQuestion[] $activeTestQuestions
 */
class ESubjectTask extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DELETE = 'delete';
    const TASK_TYPE_TASK = 11;
    const TASK_TYPE_TEST = 12;

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_subject_task';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enabled'),
            self::STATUS_DISABLE => __('Disabled'),
        ];
    }

    public static function getTaskTypeOptions()
    {
        return [
            self::TASK_TYPE_TASK => __('by task'), // Fayl ko'rinishidagi topshiriq
            self::TASK_TYPE_TEST => __('by test'), // Test ko'rinishidagi topshiriq
        ];
    }

    public function getTaskTypeLabel()
    {
        return self::getTaskTypeOptions()[$this->_task_type];
    }

    public static function getLimitBallByCurriculumSubjectDetail($curriculum = false, $subject = false, $language = false, $training_type = false, $semester = false, $employee = false, $exam_type = false)
    {
        return self::find()
            ->select('SUM(max_ball) as max_ball')
            ->where([
                '_curriculum' => $curriculum,
                '_subject' => $subject,
                '_language' => $language,
                '_training_type' => $training_type,
                '_semester' => $semester,
                '_employee' => $employee,
                '_exam_type' => $exam_type,
                'active' => self::STATUS_ENABLE
            ])
            //->orderByTranslationField('position')
            ->one();
    }

    public static function getTaskByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            //->orderByTranslationField('position')
            ->count();
    }


    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'comment', '_language', 'max_ball', 'attempt_count', 'deadline', '_exam_type', '_task_type'], 'required', 'on' => self::SCENARIO_CREATE],
            [['comment'], 'string'],
            [['_curriculum', '_subject', '_subject_topic', '_employee', 'max_ball', 'attempt_count', 'position', 'question_count', 'test_duration'], 'default', 'value' => null],
            [['_curriculum', '_subject', '_subject_topic', '_employee', 'max_ball', 'attempt_count', 'position', 'question_count', 'test_duration'], 'integer'],
            [['deadline', 'filename', '_translations', 'updated_at', 'created_at', 'published_at'], 'safe'],
            [['active', 'random'], 'boolean'],
            [['name'], 'string', 'max' => 256],
            /*[['max_ball'], 'integer', 'max' => 5 , 'min'=> 0, 'when' => function () {
                return $this->_marking_category == '12';
            }],*/
            [['_language', '_training_type', '_education_year', '_semester', '_marking_category', '_exam_type', '_final_exam_type'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_subject_topic'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumSubjectTopic::className(), 'targetAttribute' => ['_subject_topic' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_language'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['_language' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
            [['_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => ExamType::className(), 'targetAttribute' => ['_exam_type' => 'code']],
            [['_final_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => FinalExamType::className(), 'targetAttribute' => ['_final_exam_type' => 'code']],

        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSubjectTopic()
    {
        return $this->hasOne(ECurriculumSubjectTopic::className(), ['id' => '_subject_topic']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['code' => '_language']);
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function getExamType()
    {
        return $this->hasOne(ExamType::className(), ['code' => '_exam_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getSubjectTaskStudents()
    {
        return $this->hasMany(ESubjectTaskStudent::className(), ['_subject_task' => 'id']);
    }

    public function getSubjectTaskGivenStudents()
    {
        return $this->hasMany(ESubjectTaskStudent::className(), ['_subject_task' => 'id'])
            ->andOnCondition(['_task_status' => ESubjectTaskStudent::TASK_STATUS_GIVEN, 'final_active'=>1]);
    }

    public function getSubjectTaskPassedStudents()
    {
        return $this->hasMany(ESubjectTaskStudent::className(), ['_subject_task' => 'id'])
            ->andOnCondition(['_task_status' => ESubjectTaskStudent::TASK_STATUS_PASSED, 'final_active'=>1]);
    }

    public function getSubjectTaskRatedStudents()
    {
        return $this->hasMany(ESubjectTaskStudent::className(), ['_subject_task' => 'id'])
            ->andOnCondition(['_task_status' => ESubjectTaskStudent::TASK_STATUS_RATED, 'final_active'=>1]);
    }


    public function getTestQuestions()
    {
        return $this->hasMany(ESubjectResourceQuestion::className(), ['_subject_task' => 'id']);
    }

    public function getActiveTestQuestions()
    {
        return $this->hasMany(ESubjectResourceQuestion::className(), ['_subject_task' => 'id'])->where(['active' => true]);
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

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'id' => SORT_ASC],
                'attributes' => [
                    'id',
                    '_curriculum',
                    '_subject',
                    '_subject_topic',
                    '_employee',
                    '_semester',
                    '_education_year',
                    '_exam_type',
                    'position',
                    'active',
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
        if ($this->_subject_topic) {
            $query->andFilterWhere(['_subject_topic' => $this->_subject_topic]);
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
        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }

        return $dataProvider;
    }

    public function isTestTask()
    {
        return $this->_task_type == self::TASK_TYPE_TEST;
    }

    public function isRegularTask()
    {
        return $this->_task_type == self::TASK_TYPE_TASK;
    }


    public function canStartTest()
    {
        return $this->isTestTask() && $this->canSubmitTask() && $this->getTestQuestions()->where(['active' => true])->count() > 0;
    }

    public function canSubmitTask()
    {
        return time() < $this->deadline->getTimestamp();
    }
}
