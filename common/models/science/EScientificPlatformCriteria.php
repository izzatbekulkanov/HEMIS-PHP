<?php

namespace common\models\science;

use common\models\curriculum\EducationYear;
use common\models\system\_BaseModel;
use common\models\system\classifier\ScientificPlatform;
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
 * This is the model class for table "e_scientific_platform_criteria".
 *
 * @property int $id
 * @property string $_education_year
 * @property string $_publication_type_table
 * @property string $_scientific_platform
 * @property string $_criteria_type
 * @property int $mark_value
 * @property int|null $coefficient
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEducationYear $educationYear
 * @property HScientificPlatform $scientificPlatform
 */
class EScientificPlatformCriteria extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const PLATFORM_CRITERIA_HINDEX = 11;
    const PLATFORM_CRITERIA_CITATION = 12;
    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return 'e_scientific_platform_criteria';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getCriteriaTypeOptions()
    {
        return [
            self::PLATFORM_CRITERIA_HINDEX => __('h-index'),
            self::PLATFORM_CRITERIA_CITATION => __('Citation Quota'),
        ];
    }

    public static function getPublicationCriteria($publication_type_table = false, $education_year = false)
    {
        return self::find()
            ->where([
               // '_publication_type_table' => $publication_type_table,
                '_education_year' => $education_year,
                'active' => self::STATUS_ENABLE,
            ])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_education_year', '_scientific_platform', '_criteria_type', 'mark_value'], 'required', 'on' => self::SCENARIO_CREATE],
            [['mark_value', 'coefficient', 'position'], 'default', 'value' => null],
            [['mark_value', 'coefficient', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_education_year', '_publication_type_table', '_scientific_platform', '_criteria_type'], 'string', 'max' => 64],
            [['_education_year', '_scientific_platform', '_criteria_type'], 'unique', 'targetAttribute' => ['_education_year', '_scientific_platform', '_criteria_type']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_scientific_platform'], 'exist', 'skipOnError' => true, 'targetClass' => ScientificPlatform::className(), 'targetAttribute' => ['_scientific_platform' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'mark_value' => __('Criteria Value'),
        ]);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getScientificPlatform()
    {
        return $this->hasOne(ScientificPlatform::className(), ['code' => '_scientific_platform']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    '_publication_type_table',
                    '_education_year',
                    'mark_value',
                    '_scientific_platform',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        /*if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
            $query->orWhereLike('authors', $this->search);
        }*/
        if ($this->_publication_type_table) {
            $query->andFilterWhere(['_publication_type_table' => $this->_publication_type_table]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_scientific_platform) {
            $query->andFilterWhere(['_scientific_platform' => $this->_scientific_platform]);
        }
        return $dataProvider;
    }
}
