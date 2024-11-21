<?php

namespace common\models\curriculum;
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
 * This is the model class for table "e_curriculum_subject_detail".
 *
 * @property int $id
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_semester
 * @property string $_training_type
 * @property int $academic_load
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property ESubject $subject
 * @property HSemestr $semester
 * @property TrainingType $trainingType
 */
class ECurriculumSubjectDetail extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_curriculum_subject_detail';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getTrainingByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            //->orderByTranslationField('position')
            ->all();
    }

    public static function getTrainingTypeByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false, $training_type = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_training_type' => $training_type,
                'active' => self::STATUS_ENABLE
            ])
            //->orderByTranslationField('position')
            ->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
           // [['_curriculum', '_subject', '_semester', '_training_type', 'academic_load'], 'required'],
            [['_training_type', 'academic_load'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_curriculum', '_subject', 'academic_load', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', 'academic_load', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_semester', '_training_type'], 'string', 'max' => 64],
			[['_curriculum', '_subject', '_semester', '_training_type'], 'unique', 'targetAttribute' => ['_curriculum', '_subject', '_semester', '_training_type'], 'message'=>__('This training type has already been taken')],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
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

    public function getTrainingTopics()
    {
        return $this->hasMany(ECurriculumSubjectTopic::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester',  '_training_type' => '_training_type', ]);
    }

    public function getFullName()
    {
        return $this->trainingType->name .' ('. $this->academic_load. ' / '. (count($this->trainingTopics)*2).')';
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                  //  '_subject_block',
                    'id',
                    'position',
                    '_subject',
                    '_semester',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            //$query->orWhereLike('name_uz', $this->search, '_translations');
            //  $query->orWhereLike('name_oz', $this->search, '_translations');
            // $query->orWhereLike('name_ru', $this->search, '_translations');
            //$query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        /*if ($this->_subject_block) {
            $query->andFilterWhere(['_subject_block' => $this->_subject_block]);
        }*/

        return $dataProvider;
    }
}
