<?php

namespace common\models\attendance;
use common\models\curriculum\MarkingSystem;
use common\models\system\_BaseModel;
use common\models\system\classifier\AttendanceSetting;
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
 * This is the model class for table "e_attendance_setting_border".
 *
 * @property int $id
 * @property string $_attendance_setting
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
class EAttendanceSettingBorder extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_attendance_setting_border';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_attendance_setting', '_marking_system', 'min_border'], 'required', 'on' => self::SCENARIO_CREATE],
            [['min_border', 'max_border', 'position'], 'default', 'value' => null],
            [['min_border', 'max_border', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_attendance_setting', '_marking_system'], 'string', 'max' => 64],
            [['_attendance_setting'], 'exist', 'skipOnError' => true, 'targetClass' => AttendanceSetting::className(), 'targetAttribute' => ['_attendance_setting' => 'code']],
            [['_marking_system'], 'exist', 'skipOnError' => true, 'targetClass' => MarkingSystem::className(), 'targetAttribute' => ['_marking_system' => 'code']],
        ]);
    }

    public function getAttendanceSetting()
    {
        return $this->hasOne(AttendanceSetting::className(), ['code' => '_attendance_setting']);
    }

    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system']);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'code' => __('Attendance Setting'),
        ]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['_attendance_setting' => SORT_ASC],
                'attributes' => [
                    'position',
                    '_attendance_setting',
                    '_marking_system',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_marking_system) {
            $query->andFilterWhere(['_marking_system' => $this->_marking_system]);
        }
        return $dataProvider;
    }

}
