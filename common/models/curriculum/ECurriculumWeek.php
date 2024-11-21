<?php

namespace common\models\curriculum;

use common\models\system\_BaseModel;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\curriculum\ECurriculum;
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
 * This is the model class for table "e_curriculum_week".
 *
 * @property int $id
 * @property DateTime $start_date
 * @property DateTime $end_date
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_semester
 * @property string $_level
 * @property string $_education_week_type
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property ESubject $subject
 * @property Course $level
 * @property HEducationWeekType $educationWeekType
 * @property Semester $semester
 */
class ECurriculumWeek extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_curriculum_week';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getWeekByCurriculum($curriculum = false, $semester = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL,
                'active' => self::STATUS_ENABLE,
            ])
            ->orderBy(['start_date' => SORT_ASC])
            ->all();
    }

    public static function getWeekCountByCurriculum($curriculum = false, $semester = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL,
                'active' => self::STATUS_ENABLE,
            ])
            ->count();
    }

    public static function getWeekByCurriculumDate($curriculum = false, $date = "")
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['<=', 'start_date', $date])
            ->andWhere(['>=', 'end_date', $date])
            ->one();
    }

    public static function getLastWeekByCurriculumSemester($curriculum = false, $semester = "")
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL,
                'active' => self::STATUS_ENABLE
            ])
            ->orderBy(['end_date' => SORT_DESC])
            ->one();
    }

    public static function getDateByCurriculumWeekPeriod($id = false, $day = "")
    {
        $result = "";
        $week = self::find()->where(['id' => $id, 'active' => self::STATUS_ENABLE])->one();
        $day = (int)$day - 1;
        $result = $week->start_date->add(new DateInterval("P" . $day . "D"));
        return $result;
    }

    public static function getByCurriculumWeekPeriod($id = false)
    {
        return self::find()->where(['id' => $id, 'active' => self::STATUS_ENABLE])->one();
    }

    public static function getWeekByGroupCurriculum($curriculum = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL,
                'active' => self::STATUS_ENABLE,
            ])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['start_date', 'end_date', '_curriculum', '_semester', '_level', '_education_week_type'], 'required', 'on' => self::SCENARIO_CREATE],
            [['start_date', 'end_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['_curriculum', 'position'], 'default', 'value' => null],
            [['_curriculum', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_semester', '_level', '_education_week_type'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_level'], 'exist', 'skipOnError' => true, 'targetClass' => Course::className(), 'targetAttribute' => ['_level' => 'code']],
            [['_education_week_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationWeekType::className(), 'targetAttribute' => ['_education_week_type' => 'code']],
            [['_semester'], 'exist', 'skipOnError' => true, 'targetClass' => Semester::className(), 'targetAttribute' => ['_semester' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
            'position' => __('Week position'),
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
    }

    public function getEducationWeekType()
    {
        return $this->hasOne(EducationWeekType::className(), ['code' => '_education_week_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester', '_curriculum' => '_curriculum']);
    }

    public function getSubjectSchedule()
    {
        return $this->hasMany(ESubjectSchedule::className(), ['_week' => 'id', '_curriculum' => '_curriculum', '_semester' => '_semester']);
    }

    public function getFullName()
    {
        return $this->position . '. ' . Yii::$app->formatter->asDate($this->start_date) . ' - ' . Yii::$app->formatter->asDate($this->end_date);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()->with(['semester', 'level', 'educationWeekType']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject_block',
                    'position',
                    '_subject_group',
                    '_education_type',
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
            $query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['_level' => $this->_level]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }

        return $dataProvider;
    }

}
