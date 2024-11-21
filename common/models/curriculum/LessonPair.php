<?php

namespace common\models\curriculum;

use common\models\system\_BaseModel;
use common\models\curriculum\EducationYear;
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
 * This is the model class for table "h_lesson_pair".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property string $_education_year
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property HEducationYear $educationYear
 */
class LessonPair extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    const FIRST_PAIR_CODE = 10;

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'h_lesson_pair';
    }

    public static function getLessonPairByYear($education_year = false)
    {
        return self::find()
            ->where([
                '_education_year' => $education_year,
                'active' => self::STATUS_ENABLE
            ])
            ->orderByTranslationField('name')
            ->all();
    }

    public function rules()
    {
        return [
            [['name', 'start_time', 'end_time', '_education_year'], 'required', 'on' => self::SCENARIO_CREATE],
            [['position'], 'default', 'value' => null],
            [['name','position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['code', '_education_year'], 'string', 'max' => 64],
            //[['name'], 'string', 'max' => 256],
            [['start_time', 'end_time'], 'string', 'max' => 10],
            [['code', '_education_year'], 'unique', 'targetAttribute' => ['code', '_education_year']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
        ];
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }
    public function getPeriod()
    {
        return $this->start_time .' - '. $this->end_time;
    }

    public function getFullName()
    {
        return $this->name. '. '. $this->start_time .'-'. $this->end_time;
    }

    public function search($params = [])
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    '_education_year',
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
            $query->orWhereLike('code', $this->search);
            $query->orWhereLike('name', $this->search);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        return $dataProvider;
    }
}
