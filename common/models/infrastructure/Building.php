<?php

namespace common\models\infrastructure;

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
 * This is the model class for table "h_building".
 *
 * @property int $code
 * @property string $name
 * @property string $address
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EAuditorium[] $eAuditoriums
 */
class Building extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'h_building';
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
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->orderByTranslationField('name')
            ->all(), 'code', 'name');
    }

    public function rules()
    {
        return [
            [['name', 'address'], 'required',  'on'=>self::SCENARIO_INSERT],
            [['position'], 'default', 'value' => null],
            [['position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['name'], 'string', 'max' => 256],
            [['address'], 'string', 'max' => 500],
        ];
    }

    public function getEAuditoriums()
    {
        return $this->hasMany(EAuditorium::className(), ['_building' => 'code']);
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
}
