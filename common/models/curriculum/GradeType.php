<?php

namespace common\models\curriculum;
use common\models\system\_BaseModel;
use common\models\curriculum\MarkingSystem;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\conditions\InCondition;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "h_grade_type".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $_marking_system
 * @property int|null $min_border
 * @property int|null $max_border
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property HMarkingSystem $markingSystem
 */
class GradeType extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    const GRADE_TYPE_FIVE = '11';
    const GRADE_TYPE_FOUR = '12';
    const GRADE_TYPE_THREE = '13';
    const GRADE_TYPE_TWO = '14';

    const CURRENT_STATUS = 1;
	const SCENARIO_CREATE = 'create';
	
	protected $_translatedAttributes = ['name'];
	
    public static function tableName()
    {
        return 'h_grade_type';
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

    /**
     * @param false $marking_system
     * @param false $grade_type
     * @return self
     */
    public static function getGradeByCode($marking_system = false, $grade_type = false)
    {
        return self::find()
            ->where([
                '_marking_system' => $marking_system,
                'code' => $grade_type,
                'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['code', 'name', '_marking_system', 'min_border', 'max_border',], 'required', 'on' => self::SCENARIO_CREATE],
            [['min_border', 'max_border', 'position'], 'default', 'value' => null],
            [['position'], 'integer'],
            [['min_border', 'max_border'], 'number'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['code', '_marking_system'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['code', '_marking_system'], 'unique', 'targetAttribute' => ['code', '_marking_system']],
            [['_marking_system'], 'exist', 'skipOnError' => true, 'targetClass' => MarkingSystem::className(), 'targetAttribute' => ['_marking_system' => 'code']],
        ]);
    }

    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system']);
    }
	
	public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['_marking_system' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'position',
				    '_marking_system',
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
		if ($this->_marking_system) {
            $query->andFilterWhere(['_marking_system' => $this->_marking_system]);
        }
		
        return $dataProvider;
    }

    public static function getGradeForBall($marking_system, $grade)
    {
        return self::find()
            ->where([
                        '_marking_system' => $marking_system,
                        'active' => self::STATUS_ENABLE
                    ])
            ->andWhere(['>=', 'max_border', $grade])
            ->andWhere(['<=', 'min_border', $grade])
            ->one();
    }
}
