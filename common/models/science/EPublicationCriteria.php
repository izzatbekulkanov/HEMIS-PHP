<?php

namespace common\models\science;

use common\models\curriculum\EducationYear;
use common\models\system\_BaseModel;
use common\models\system\classifier\MethodicalPublicationType;
use common\models\system\classifier\PatientType;
use common\models\system\classifier\ScientificPublicationType;
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
 * This is the model class for table "e_publication_criteria".
 *
 * @property int $id
 * @property string $_education_year
 * @property string $_publication_type_table
 * @property string|null $_publication_methodical_type
 * @property string|null $_publication_scientific_type
 * @property string|null $_publication_property_type
 * @property int|null $_in_publication_database
 * @property int $mark_value
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEducationYear $educationYear
 * @property HMethodicalPublicationType $publicationMethodicalType
 * @property HPatientType $publicationPropertyType
 * @property HScientificPublicationType $publicationScientificType
 */
class EPublicationCriteria extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    protected $_translatedAttributes = [];
    public static function tableName()
    {
        return 'e_publication_criteria';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getPublicationCriteria($publication_type_table = false, $education_year = false)
    {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_education_year' => $education_year,
                    'active' => self::STATUS_ENABLE,
                ])
                ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_education_year', '_publication_type_table', 'mark_value'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_in_publication_database', 'mark_value', 'position', 'exist_certificate'], 'default', 'value' => null],
            [['_in_publication_database', 'mark_value', 'position', 'exist_certificate'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_education_year', '_publication_type_table', '_publication_methodical_type', '_publication_scientific_type', '_publication_property_type'], 'string', 'max' => 64],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_publication_methodical_type'], 'exist', 'skipOnError' => true, 'targetClass' => MethodicalPublicationType::className(), 'targetAttribute' => ['_publication_methodical_type' => 'code']],
            [['_publication_property_type'], 'exist', 'skipOnError' => true, 'targetClass' => PatientType::className(), 'targetAttribute' => ['_publication_property_type' => 'code']],
            [['_publication_scientific_type'], 'exist', 'skipOnError' => true, 'targetClass' => ScientificPublicationType::className(), 'targetAttribute' => ['_publication_scientific_type' => 'code']],
        ]);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getPublicationMethodicalType()
    {
        return $this->hasOne(MethodicalPublicationType::className(), ['code' => '_publication_methodical_type']);
    }

    public function getPublicationPropertyType()
    {
        return $this->hasOne(PatientType::className(), ['code' => '_publication_property_type']);
    }

    public function getPublicationScientificType()
    {
        return $this->hasOne(ScientificPublicationType::className(), ['code' => '_publication_scientific_type']);
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
                    '_publication_methodical_type',
                    '_publication_scientific_type',
                    '_publication_property_type',
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
       /* if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }
        if ($this->_publication_database) {
            $query->andFilterWhere(['_publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }
}
