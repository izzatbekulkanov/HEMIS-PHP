<?php

namespace common\models\infrastructure;

use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\LessonPair;
use common\models\system\_BaseModel;
use common\models\system\classifier\AuditoriumType;
use DateInterval;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_auditorium".
 *
 * @property int $code
 * @property string $name
 * @property int|null $_building
 * @property string $_auditorium_type
 * @property int $volume
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property AuditoriumType $auditoriumType
 * @property Building $building
 */
class EAuditorium extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_auditorium';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getOptions()
    {
        return self::find()
            ->where([
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('name')
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['week', '_education_year'], 'safe'],
            [['name', '_building', '_auditorium_type', 'volume'], 'required', 'on' => self::SCENARIO_INSERT],
            [['_building', 'volume', 'position'], 'integer', 'min' => 0, 'max' => 10000],
            [['name'], 'string', 'max' => 256],
            [['_auditorium_type'], 'exist', 'skipOnError' => true, 'targetClass' => AuditoriumType::className(), 'targetAttribute' => ['_auditorium_type' => 'code']],
            [['_building'], 'exist', 'skipOnError' => true, 'targetClass' => Building::className(), 'targetAttribute' => ['_building' => 'code']],
        ]);
    }

    public function getAuditoriumType()
    {
        return $this->hasOne(AuditoriumType::className(), ['code' => '_auditorium_type']);
    }

    public function getBuilding()
    {
        return $this->hasOne(Building::className(), ['code' => '_building']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'name' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'volume',
                    'position',
                    '_auditorium_type',
                    '_building',
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

        if ($this->_building) {
            $query->andFilterWhere(['_building' => $this->_building]);
        }
        if ($this->_auditorium_type) {
            $query->andFilterWhere(['_auditorium_type' => $this->_auditorium_type]);
        }
        return $dataProvider;
    }

    public $week;
    public $_education_year;

    public function searchForAuditorium($params)
    {
        $data = [];

        $this->load($params);
        $currentYear = EducationYear::getCurrentYear()->code;

        if ($this->_building == null) {
            if ($options = array_keys($this->getBuildingOptions())) {
                if ($value = array_shift($options)) {
                    $this->_building = $value;
                }
            }
        }

        if ($this->week == null) {
            if ($options = array_keys($this->getWeekOptions())) {
                if ($value = array_shift($options)) {
                    $this->week = $value;
                }
            }
        }

        if ($this->week && $this->_building) {
            $rooms = self::find()
                ->where(['_building' => $this->_building, 'active' => true])
                ->orderBy(['name' => SORT_ASC])
                ->all();


            $pairs = [];
            foreach (LessonPair::getLessonPairByYear($currentYear) as $pair) {
                $pairs[$pair->code] = [
                    'label' => $pair->name,
                    'count' => 0,
                ];
            }

            $start = date_create_from_format('Y-m-d', $this->week);
            $days = [];
            foreach (range(0, 5) as $day) {
                $days[$start->format('Y-m-d')] = [
                    'label' => upperCaseFirst(Yii::$app->formatter->asDate($start->getTimestamp(), 'php:l, d-m-Y')),
                    'pairs' => $pairs
                ];
                $start->add(new DateInterval("P1D"));
            }

            foreach ($rooms as $room) {
                $data[$room->code] = [
                    'room' => $room,
                    'label' => $room->name,
                    'days' => $days
                ];
            }

            $lessons = ESubjectSchedule::find()
                ->select([new Expression('lesson_date, _auditorium, _lesson_pair, count(1) as count')])
                ->andWhere(['>=', 'lesson_date', $this->week])
                ->andWhere(['<=', 'lesson_date', $start->format('Y-m-d')])
                ->andFilterWhere(['_auditorium' => ArrayHelper::getColumn($rooms, 'code')])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                ->groupBy(['lesson_date', '_auditorium', '_lesson_pair'])
                ->asArray()
                ->all();


            foreach ($lessons as $lesson) {
                if (@isset($data[$lesson['_auditorium']]['days'][$lesson['lesson_date']]['pairs'][$lesson['_lesson_pair']])) {
                    $data[$lesson['_auditorium']]['days'][$lesson['lesson_date']]['pairs'][$lesson['_lesson_pair']]['count'] = $lesson['count'];
                }
            }

            return [
                'rooms' => $data,
                'pairs' => $pairs,
                'days' => $days
            ];
        }

        return [];
    }

    public function getBuildingOptions()
    {
        return \common\models\infrastructure\Building::getOptions();
    }

    private $_weekOptions;

    public function getWeekOptions()
    {
        if ($this->_weekOptions == null) {
            $currentYear = EducationYear::getCurrentYear()->code;
            $weeks = ESubjectSchedule::find()
                ->orderBy(['week' => SORT_ASC])
                ->where(['_education_year' => $currentYear])
                ->select([new Expression("date_trunc('week', lesson_date::date)::date AS week")])
                ->groupBy(['week'])
                ->asArray()
                ->all();

            $this->_weekOptions = ArrayHelper::map($weeks, 'week', function ($item) {
                $date = date_create_from_format('Y-m-d', $item['week']);

                return sprintf("%s - %s",
                    Yii::$app->formatter->asDate($date->getTimestamp(), 'php:d F'),
                    Yii::$app->formatter->asDate($date->add(new DateInterval('P5D'))->getTimestamp(), 'php:d F, Y')
                );
            });
        }

        return $this->_weekOptions;
    }
}
