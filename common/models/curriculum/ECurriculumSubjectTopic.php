<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\TrainingType;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ESubject;
use common\models\curriculum\Semester;
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
 * This is the model class for table "e_curriculum_subject_topic".
 *
 * @property int $id
 * @property string $name
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_semester
 * @property string $_training_type
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property ESubject $subject
 * @property Semester $semester
 * @property TrainingType $trainingType
 */
class ECurriculumSubjectTopic extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_DEPARTMENT = 'create';
    const SCENARIO_CREATE_TEST = 'test';

    protected $_translatedAttributes = ['name'];
    protected $_booleanAttributes = ['random'];

    public static function tableName()
    {
        return 'e_curriculum_subject_topic';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getTopicByCurriculumSemesterSubjectTraining($curriculum = false, $semester = false, $subject = false, $training_type = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_training_type' => $training_type,
                'active' => self::STATUS_ENABLE
            ])
            ->orderBy(['position' => SORT_ASC, 'id' => SORT_ASC])
            //->orderByTranslationField('position')
            ->all();
    }

    public static function getTopicByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->orderBy(['position' => SORT_ASC, 'id' => SORT_ASC])
            //->orderByTranslationField('position')
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', '_subject', '_semester', '_training_type'], 'required', 'on' => self::SCENARIO_CREATE],
            [['name', '_training_type'], 'required', 'on' => self::SCENARIO_CREATE_DEPARTMENT],
            [['_curriculum', '_subject', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', 'position', '_department'], 'integer'],
            [['active'], 'boolean'],
            [['random'], 'safe'],
            [['name'], 'string', 'max' => 500],
            [['_semester', '_training_type'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_semester'], 'exist', 'skipOnError' => true, 'targetClass' => Semester::className(), 'targetAttribute' => ['_semester' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
    }

    public function getFullName()
    {
        return trim($this->position . '. ' . $this->name);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getSubjectResources()
    {
        return $this->hasMany(ESubjectResource::className(), ['_subject_topic' => 'id']);
    }

    public function getQuestions()
    {
        return $this->hasMany(ESubjectResourceQuestion::class, ['_subject_topic' => 'id'])->orderBy(['position' => SORT_ASC]);
    }

    public function getQuestionsDataProvider()
    {
        return new ActiveDataProvider(
            [
                'query' => $this->getQuestions(),
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );
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
                    'position',
                    'name',
                    '_curriculum',
                    '_subject',
                    '_semester',
                    '_training_type',
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
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }

        if ($this->_training_type) {
            $query->andFilterWhere(['_training_type' => $this->_training_type]);
        }

        return $dataProvider;
    }

    public function getEmployeeResources(EEmployee $employee, $language, $type = ESubjectResource::RESOURCE_TYPE_RESOURCE)
    {
        return ESubjectResource::find()
            ->where([
                '_curriculum' => $this->_curriculum,
                '_semester' => $this->_semester,
                '_training_type' => $this->_training_type,
                '_subject_topic' => $this->id,
                '_language' => $language,
                '_employee' => $employee->id,
                'resource_type' => $type,
                'active' => self::STATUS_ENABLE
            ])
            ->all();
    }
}
