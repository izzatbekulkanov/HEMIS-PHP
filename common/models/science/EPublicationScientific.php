<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\curriculum\EducationYear;
use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\classifier\Country;
use common\models\system\classifier\Language;
use common\models\system\classifier\Locality;
use common\models\system\classifier\PublicationDatabase;
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
 * This is the model class for table "e_publication_scientific".
 *
 * @property int $id
 * @property string $name
 * @property string $keywords
 * @property string $authors
 * @property string $doi
 * @property int $author_counts
 * @property string $source_name
 * @property int $issue_year
 * @property string $parameter
 * @property string $_scientific_publication_type
 * @property string|null $_publication_database
 * @property string $_locality
 * @property string|null $_country
 * @property int $_employee
 * @property int|null $position
 * @property bool|null $active
 * @property bool|null $is_checked
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property string[] $filename
 * @property DateTime $is_checked_date
 *
 * @property EEmployee $employee
 * @property EducationYear $educationYear
 * @property EPublicationAuthorMeta[] $publicationAuthors
 * @property Country $country
 * @property Locality $locality
 * @property PublicationDatabase $publicationDatabase
 * @property ScientificPublicationType $scientificPublicationType
 */
class EPublicationScientific extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_AUTHOR = 'create_author';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_publication_scientific';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getYearOptions()
    {
        $years = [];

        for ($i = date('Y'); $i > (date('Y') - 11); $i--)
            $years [$i] = $i;

        return $years;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'keywords', 'authors', 'author_counts', 'source_name', 'issue_year', 'parameter', '_scientific_publication_type', '_employee', 'filename', '_publication_database', '_education_year',], 'required', 'on' => self::SCENARIO_CREATE],
            [['name', 'keywords', 'authors', 'author_counts', 'source_name', 'issue_year', 'parameter', '_scientific_publication_type', 'filename', '_publication_database', '_education_year', '_language'], 'required', 'on' => self::SCENARIO_CREATE_AUTHOR],
            [['author_counts', 'issue_year', '_employee', 'position'], 'default', 'value' => null],
            [['author_counts', 'issue_year', '_employee', 'position'], 'integer'],
            [['active', 'is_checked'], 'boolean'],
            [['filename', '_translations', 'updated_at', 'created_at', 'is_checked_date'], 'safe'],
            [['name', 'keywords', 'source_name', 'parameter'], 'string', 'max' => 500],
            [['authors', 'doi'], 'string', 'max' => 255],
            [['_scientific_publication_type', '_publication_database', '_locality', '_country', '_education_year', '_language'], 'string', 'max' => 64],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
            [['_locality'], 'exist', 'skipOnError' => true, 'targetClass' => Locality::className(), 'targetAttribute' => ['_locality' => 'code']],
            [['_publication_database'], 'exist', 'skipOnError' => true, 'targetClass' => PublicationDatabase::className(), 'targetAttribute' => ['_publication_database' => 'code']],
            [['_scientific_publication_type'], 'exist', 'skipOnError' => true, 'targetClass' => ScientificPublicationType::className(), 'targetAttribute' => ['_scientific_publication_type' => 'code']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_language'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['_language' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => __('Name of Scientific Publication'),
        ]);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getLocality()
    {
        return $this->hasOne(Locality::className(), ['code' => '_locality']);
    }

    public function getPublicationDatabase()
    {
        return $this->hasOne(PublicationDatabase::className(), ['code' => '_publication_database']);
    }

    public function getScientificPublicationType()
    {
        return $this->hasOne(ScientificPublicationType::className(), ['code' => '_scientific_publication_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['code' => '_language']);
    }

    public function getPublicationAuthors()
    {
        return $this->hasMany(EPublicationAuthorMeta::className(), ['_publication_scientific' => 'id']);
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
                    'name',
                    'authors',
                    'publisher',
                    'issue_year',
                    '_scientific_publication_type',
                    '_publication_database',
                    '_employee',
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
        if ($this->_scientific_publication_type) {
            $query->andFilterWhere(['_scientific_publication_type' => $this->_scientific_publication_type]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }
        if ($this->_publication_database) {
            $query->andFilterWhere(['_publication_database' => $this->_publication_database]);
        }
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return $this->name;
    }

    public function canBeDeleted()
    {
        return $this->is_checked == false;
    }
    public function canBeUpdated()
    {
        return $this->is_checked == false;
    }
}
