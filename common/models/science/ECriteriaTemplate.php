<?php

namespace common\models\science;

use common\models\system\_BaseModel;
use common\models\system\classifier\MethodicalPublicationType;
use common\models\system\classifier\PatientType;
use common\models\system\classifier\ScientificPlatform;
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
 * This is the model class for table "e_criteria_template".
 *
 * @property int $id
 * @property string $_publication_type_table
 * @property string|null $_publication_methodical_type
 * @property string|null $_publication_scientific_type
 * @property string|null $_publication_property_type
 * @property int|null $_in_publication_database
 * @property int|null $exist_certificate
 * @property int $mark_value
 * @property string|null $_scientific_platform
 * @property string|null $_criteria_type
 * @property int|null $coefficient
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property HMethodicalPublicationType $publicationMethodicalType
 * @property HPatientType $publicationPropertyType
 * @property HScientificPlatform $scientificPlatform
 * @property HScientificPublicationType $publicationScientificType
 */
class ECriteriaTemplate extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const PUBLICATION_TYPE_METHODICAL = 11;
    const PUBLICATION_TYPE_SCIENTIFIC = 12;
    const PUBLICATION_TYPE_PROPERTY = 13;
    const PUBLICATION_TYPE_ACTIVITY = 14;
    protected $_translatedAttributes = [];
    public static function tableName()
    {
        return 'e_criteria_template';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getPublicationTypeOptions()
    {
        return [
            self::PUBLICATION_TYPE_METHODICAL => __('Methodical Publication'),
            self::PUBLICATION_TYPE_SCIENTIFIC => __('Scientific Publication'),
            self::PUBLICATION_TYPE_PROPERTY => __('Property Publication'),
            self::PUBLICATION_TYPE_ACTIVITY => __('Scientific Activity'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_publication_type_table', 'mark_value', 'updated_at', 'created_at'], 'required'],
            [['_in_publication_database', 'exist_certificate', 'mark_value', 'coefficient', 'position'], 'default', 'value' => null],
            [['_in_publication_database', 'exist_certificate', 'mark_value', 'coefficient', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_publication_type_table', '_publication_methodical_type', '_publication_scientific_type', '_publication_property_type', '_scientific_platform', '_criteria_type'], 'string', 'max' => 64],
            [['_publication_methodical_type'], 'exist', 'skipOnError' => true, 'targetClass' => MethodicalPublicationType::className(), 'targetAttribute' => ['_publication_methodical_type' => 'code']],
            [['_publication_property_type'], 'exist', 'skipOnError' => true, 'targetClass' => PatientType::className(), 'targetAttribute' => ['_publication_property_type' => 'code']],
            [['_scientific_platform'], 'exist', 'skipOnError' => true, 'targetClass' => ScientificPlatform::className(), 'targetAttribute' => ['_scientific_platform' => 'code']],
            [['_publication_scientific_type'], 'exist', 'skipOnError' => true, 'targetClass' => ScientificPublicationType::className(), 'targetAttribute' => ['_publication_scientific_type' => 'code']],
        ]);
    }

    public function getPublicationMethodicalType()
    {
        return $this->hasOne(MethodicalPublicationType::className(), ['code' => '_publication_methodical_type']);
    }

    public function getPublicationPropertyType()
    {
        return $this->hasOne(PatientType::className(), ['code' => '_publication_property_type']);
    }

    public function getScientificPlatform()
    {
        return $this->hasOne(ScientificPlatform::className(), ['code' => '_scientific_platform']);
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
        /* if ($this->_employee) {
             $query->andFilterWhere(['_employee' => $this->_employee]);
         }
         if ($this->_publication_database) {
             $query->andFilterWhere(['_publication_database' => $this->_publication_database]);
         }*/
        return $dataProvider;
    }
}
