<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\classifier\Language;
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

/**
 * This is the model class for table "e_subject_resource".
 *
 * @property int $id
 * @property string $name
 * @property string $comment
 * @property int $test_duration
 * @property int $test_questions
 * @property int $test_attempt_count
 * @property int $test_question_count
 * @property boolean $test_random
 * @property int $_curriculum
 * @property int $resource_type
 * @property int $_subject
 * @property string $_language
 * @property int|null $_subject_topic
 * @property string $_education_year
 * @property string $_training_type
 * @property string $_semester
 * @property string[] $filename
 * @property int $_employee
 * @property string|null $image
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property string|null $published_at
 *
 * @property ECurriculum $curriculum
 * @property TrainingType $trainingType
 * @property ECurriculumSubjectTopic $subjectTopic
 * @property EEmployee $employee
 * @property ESubject $subject
 * @property EducationYear $educationYear
 * @property Language $language
 * @property ESubjectResourceQuestion[] $testQuestions
 * @property ESubjectResourceQuestion[] $activeTestQuestions
 */
class ESubjectResource extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_RESOURCE = 'resource';
    const SCENARIO_TEST = 'test';
    const SCENARIO_DELETE = 'delete';
    const RESOURCE_TYPE_RESOURCE = 11;
    const RESOURCE_TYPE_TEST = 12;

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_subject_resource';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getResourceByTopicLanguageEmployee($curriculum = false, $semester = false, $subject = false, $training_type = false, $topic = false, $language = false, $employee = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_training_type' => $training_type,
                '_subject_topic' => $topic,
                '_language' => $language,
                '_employee' => $employee,
                'resource_type' => self::RESOURCE_TYPE_RESOURCE,
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('position')
            ->all();
    }

    public static function getResourceByLanguageEmployee($curriculum = false, $semester = false, $subject = false, $training_type = false, $language = false, $employee = false)
    {
        $query = self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_language' => $language,
                '_employee' => $employee,
                'active' => self::STATUS_ENABLE,
                'resource_type' => ESubjectResource::RESOURCE_TYPE_RESOURCE,
            ])
            ->orderBy(['position' => SORT_ASC]);

        if ($training_type) {
            $query->andFilterWhere(['_training_type' => $training_type]);
        }
        return $query->all();
    }

    public static function getResourceBySemester($curriculum = false, $semester = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('position')
            ->count();
    }

    public static function getResourceBySemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('position')
            ->count();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'comment', '_language'], 'required', 'on' => self::SCENARIO_RESOURCE],
            [['test_duration', 'test_questions', 'test_attempt_count', 'test_random'], 'safe', 'on' => self::SCENARIO_TEST],
            //[['test_duration', 'test_questions', 'test_attempt_count'], 'number', 'integerOnly' => true],
            [['comment'], 'string'],
            [['_curriculum', '_subject', '_subject_topic', '_employee', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', '_subject_topic', '_employee', 'position'], 'integer'],
            [['filename', '_translations', 'updated_at', 'created_at', 'published_at'], 'safe'],
            [['active'], 'boolean'],
            [['name'], 'string', 'max' => 256],
            [['path'], 'string', 'max' => 500],
            [['_language', '_education_year', '_semester', '_training_type'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_subject_topic'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumSubjectTopic::className(), 'targetAttribute' => ['_subject_topic' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_language'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['_language' => 'code']],
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

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function getTestQuestions()
    {
        return $this->hasMany(ESubjectResourceQuestion::className(), ['_subject_resource' => 'id']);
    }

    public function beforeSave($insert)
    {
        $this->test_random = boolval($this->test_random);
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }


    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_subject_topic',
                    '_employee',
                    '_semester',
                    '_education_year',
                    '_training_type',
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
        if ($this->_training_type) {
            $query->andFilterWhere(['_training_type' => $this->_training_type]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }

        return $dataProvider;
    }

    /**
     * @param ECurriculumSubjectTopic $topic_model
     * @param $employeeId
     * @param $educationLang
     * @return self
     */
    public static function getTopicTestResource(ECurriculumSubjectTopic $topic_model, $employeeId, $educationLang)
    {
        $educationYear = Semester::getByCurriculumSemester($topic_model->_curriculum, $topic_model->_semester);

        return self::findOne([
            'resource_type' => ESubjectResource::RESOURCE_TYPE_TEST,
            '_curriculum' => $topic_model->_curriculum,
            '_subject' => $topic_model->_subject,
            '_language' => $educationLang,
            '_training_type' => $topic_model->_training_type,
            '_subject_topic' => $topic_model->id,
            '_education_year' => $educationYear->educationYear->code,
            '_semester' => $topic_model->_semester,
            '_employee' => $employeeId,
        ]);
    }

    public function updateQuestionsCount()
    {
        return $this->updateAttributes(['test_question_count' => ESubjectResourceQuestion::find()->where(['_subject_resource' => $this->id])->count()]);
    }

    public function canStartTest()
    {
        return $this->getTestQuestions()->where(['active' => true])->count() > 0;
    }
}
