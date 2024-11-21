<?php

namespace common\models\curriculum;
use common\models\system\_BaseModel;
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
 * This is the model class for table "h_rating_grade".
 *
 * @property string $code
 * @property string $name
 * @property string $template
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculumSubject[] $eCurriculumSubjects
 */
class RatingGrade extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    const RATING_GRADE_SUBJECT = '11';
    const RATING_GRADE_COURSE = '12';
    const RATING_GRADE_STATE = '13';
    const RATING_GRADE_PRACTICUM = '14';
    const RATING_GRADE_GRADUATE = '15';
    const RATING_GRADE_SUBJECT_FINAL = '16';
    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'h_rating_grade';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getShortOptions()
    {
        return [
            self::RATING_GRADE_SUBJECT => 'FQA',
            self::RATING_GRADE_COURSE => 'KIQ',
            self::RATING_GRADE_STATE => 'YAQ',
            self::RATING_GRADE_PRACTICUM => 'MAQ',
            self::RATING_GRADE_GRADUATE => 'BIQ',
            self::RATING_GRADE_SUBJECT_FINAL => 'FQQ',
        ];
    }

    public static function getOptions()
    {
        $items = self::find()
            ->where(['active' => true])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['code', 'name', 'template'], 'required', 'on' => self::SCENARIO_CREATE],
            [['position'], 'default', 'value' => null],
            [['position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name', 'template'], 'string', 'max' => 256],
            [['code'], 'unique'],
        ]);
    }

    public function getECurriculumSubjects()
    {
        return $this->hasMany(ECurriculumSubject::className(), ['_rating_grade' => 'code']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'template',
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

        return $dataProvider;
    }
}
