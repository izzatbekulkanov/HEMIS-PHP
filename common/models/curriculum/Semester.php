<?php

namespace common\models\curriculum;

use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\classifier\Course;
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
 * This is the model class for table "h_semestr".
 *
 * @property string $code
 * @property string $name
 * @property int $_curriculum
 * @property string $_education_year
 * @property string $_level
 * @property DateTime $start_date
 * @property DateTime $end_date
 * @property int|null $position
 * @property bool|null $active
 * @property bool|null $last
 * @property bool|null $accepted
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EStudentMeta[] $eStudentMeta
 * @property ECurriculum $curriculum
 * @property EducationYear $educationYear
 * @property Course $level
 */
class Semester extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SEMESTER_FIRST = '11';
    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'h_semestr';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->active]) ? $labels[$this->active] : '';
    }

    public static function getByCurriculumYear($curriculum = false, $education_year = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('name')
            ->all();
    }

    public static function getSemesterByCurriculum($curriculum = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('name')
            ->all();
    }

    /**
     * @param false $curriculum
     * @param false $semester
     * @return self
     */
    public static function getByCurriculumSemester($curriculum = false, $semester = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                'code' => $semester,
                //  'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public function getByCurrentSemester()
    {
        return self::find()
            ->where([
                '_curriculum' => $this->_curriculum,
                'code' => $this->code,
            ])
            ->one()->name;
    }

    public static function getCourseOptions($curriculum = false, $education_year = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
              //  'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('name')
            ->all();
    }

    public static function getCourseCode($curriculum = false, $education_year = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
                //  'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public static function getOptionsByCurriculumSemester($curriculum = false, $semester = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                'code' => $semester,
                //  'active' => self::STATUS_ENABLE
            ])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['code', 'name', '_curriculum', '_education_year', 'start_date', 'end_date'], 'required', 'on' => self::SCENARIO_CREATE],
            [['code', '_curriculum', '_education_year', 'start_date', 'end_date', '_level'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_curriculum', 'position'], 'default', 'value' => null],
            [['_curriculum', 'position'], 'integer'],
            [['start_date', 'end_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active', 'accepted'], 'boolean'],
            [['code', '_education_year'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['code', '_curriculum'], 'unique', 'targetAttribute' => ['code', '_curriculum']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_level'], 'exist', 'skipOnError' => true, 'targetClass' => Course::className(), 'targetAttribute' => ['_level' => 'code']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
    }

    public function getEStudentMeta()
    {
        return $this->hasMany(EStudentMeta::className(), ['_semestr' => 'code']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()->with(['level','curriculum','educationYear']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'position',
                    '_curriculum',
                    '_education_year',
                    'start_date',
                    'end_date',
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
            $query->orWhereLike('name', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        return $dataProvider;
    }

    public function getSemesterNumber()
    {
        return preg_replace('/[^0-9]/', '', $this->name);
    }
}
