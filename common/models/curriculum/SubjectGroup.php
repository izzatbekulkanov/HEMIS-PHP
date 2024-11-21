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
 * This is the model class for table "h_subject_group".
 *
 * @property string $code
 * @property string $name
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_parent
 * @property string|null $_translations
 * @property string|null $_options
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ESubject[] $eSubjects
 * @property SubjectGroup $parent
 * @property SubjectGroup[] $subjectGroups
 */
class SubjectGroup extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'h_subject_group';
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
            [['code', 'name'], 'required', 'on' => self::SCENARIO_CREATE],
            [['position'], 'default', 'value' => null],
            [['position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', '_options', 'updated_at', 'created_at'], 'safe'],
            [['code', '_parent'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['code'], 'unique'],
            [['_parent'], 'exist', 'skipOnError' => true, 'targetClass' => SubjectGroup::className(), 'targetAttribute' => ['_parent' => 'code']],
        ]);
    }

    public function getESubjects()
    {
        return $this->hasMany(ESubject::className(), ['_subject_group' => 'code']);
    }

    public function getParent()
    {
        return $this->hasOne(SubjectGroup::className(), ['code' => '_parent']);
    }

    public function getSubjectGroups()
    {
        return $this->hasMany(SubjectGroup::className(), ['_parent' => 'code']);
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
}
