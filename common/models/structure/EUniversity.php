<?php

namespace common\models\structure;

use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\models\system\_BaseModel;
use common\models\system\classifier\Ownership;
use common\models\system\classifier\Soato;
use common\models\system\classifier\University;
use common\models\system\classifier\UniversityForm;
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
 * This is the model class for table "e_university".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $tin
 * @property string $address
 * @property string $contact
 * @property string $_ownership
 * @property string $_soato
 * @property string $_university_type
 * @property string $_university_form
 *
 * @property EDepartment[] $eDepartments
 * @property Ownership $ownership
 * @property Soato $soato
 * @property University $university
 * @property UniversityForm $universityForm
 */
class EUniversity extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    protected $_translatedAttributes = ['name', 'mailing_address', 'accreditation_info', 'address'];

    public static function tableName()
    {
        return 'e_university';
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
            [['code', 'name', 'address', 'contact', '_ownership', '_university_form', 'mailing_address', 'bank_details', 'accreditation_info'], 'required'],
            [['name', 'address'], 'string', 'max' => 500],
            [['contact'], 'string', 'max' => 255],
            [['_ownership', '_university_form', '_soato'], 'string', 'max' => 64],
            [['mailing_address', 'bank_details', 'accreditation_info'], 'string', 'max' => 32000],
            [['_ownership'], 'exist', 'skipOnError' => true, 'targetClass' => Ownership::class, 'targetAttribute' => ['_ownership' => 'code']],
            [['_university_form'], 'exist', 'skipOnError' => true, 'targetClass' => UniversityForm::class, 'targetAttribute' => ['_university_form' => 'code']],
            [['_soato'], 'exist', 'skipOnError' => true, 'targetClass' => Soato::class, 'targetAttribute' => ['_soato' => 'code']],
            [['code'], 'exist', 'skipOnError' => true, 'targetClass' => University::class, 'targetAttribute' => ['code' => 'code']],
            [['tin'], 'match', 'pattern' => '/^[0-9]{9}$/', 'message' => __('INN xato kiritildi')],
        ]);
    }

    public function getEDepartments()
    {
        return $this->hasMany(EDepartment::className(), ['_university' => 'id']);
    }

    public function getOwnership()
    {
        return $this->hasOne(Ownership::className(), ['code' => '_ownership']);
    }

    public function getUniversity()
    {
        return $this->hasOne(University::className(), ['code' => 'code']);
    }

    public function getUniversityForm()
    {
        return $this->hasOne(UniversityForm::className(), ['code' => '_university_form']);
    }

    public function getSoato()
    {
        return $this->hasOne(Soato::className(), ['code' => '_soato']);
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
                    'address',
                    'code',
                    'mailing_address',
                    'bank_details',
                    'contact',
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
            $query->orWhereLike('code', $this->search);
            $query->orWhereLike('name', $this->search);
            $query->orWhereLike('address', $this->search);
            $query->orWhereLike('address', $this->search);
            $query->orWhereLike('contact', $this->search);
        }

        return $dataProvider;
    }

    protected static $_instance = null;

    /**
     * @return self
     */
    public static function findCurrentUniversity()
    {
        if (self::$_instance == null) {
            self::$_instance = self::find()->limit(1)->one();
        }

        return self::$_instance;
    }


    public function getDescriptionForSync()
    {
        return $this->name;
    }
}
