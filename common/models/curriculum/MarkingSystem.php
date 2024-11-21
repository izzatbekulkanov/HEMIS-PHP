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
 * This is the model class for table "h_marking_system".
 *
 * @property string $code
 * @property string $name
 * @property int|null $minimum_limit
 * @property int|null $position
 * @property float|null $gpa_limit
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 */
class MarkingSystem extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const CURRENT_STATUS = 1;
    const SCENARIO_CREATE = 'create';

    const MARKING_SYSTEM_RATING = '11';
    const MARKING_SYSTEM_FIVE = '12';
    const MARKING_SYSTEM_CREDIT = '13';
    protected $_translatedAttributes = ['description'];
    public static function tableName()
    {
        return 'h_marking_system';
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

    public static function getOptions()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->orderByTranslationField('name')
            ->all(), 'code', 'name');
    }

    public static function getOptionsForTask()
    {
        return [
            self::MARKING_SYSTEM_RATING => __('100 point'),
            self::MARKING_SYSTEM_FIVE => __('5 point'),
        ];
    }

    public static function getCreditScale()
    {
        return "Baholash tizimi / Grading system: <br>
                A = 4.26 - 4.5 (86-90); &nbsp;&nbsp;    A+=4.51 - 5.0 (91-100);<br>
                B = 3.51 - 4.0 (71-80); &nbsp;&nbsp;    B+=4.01 - 4.25 (81-85); <br>
                C = 3.0 - 3.25 (60-65); &nbsp;&nbsp;    C+=3.26 - 3.5 (66-70).";
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['code', 'name'], 'required', 'on' => self::SCENARIO_CREATE],
            [['minimum_limit', 'count_final_exams', 'position'], 'default', 'value' => null],
            [['minimum_limit', 'count_final_exams', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'description', 'updated_at', 'created_at'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['code'], 'unique'],
            [['gpa_limit'], 'number', 'min' => 2.4],
            [['gpa_limit'], 'required', 'when' => function () {
                return $this->isCreditMarkingSystem() && $this->isNewRecord == false;
            }],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
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
            $query->orWhereLike('name', $this->search);
        }

        return $dataProvider;
    }

    public function isCreditMarkingSystem()
    {
        return $this->code == self::MARKING_SYSTEM_CREDIT;
    }

    public function isRatingSystem()
    {
        return $this->code == self::MARKING_SYSTEM_RATING;
    }

    public function isFiveMarkSystem()
    {
        return $this->code == self::MARKING_SYSTEM_FIVE;
    }

}
