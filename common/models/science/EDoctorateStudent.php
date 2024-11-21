<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\system\_BaseModel;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Country;
use common\models\system\classifier\DoctoralStudentType;
use common\models\system\classifier\DoctorateStudentStatus;
use common\models\system\classifier\Gender;
use common\models\system\classifier\Nationality;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\ScienceBranch;
use common\models\system\classifier\Soato;
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
 * This is the model class for table "e_doctorate_student".
 *
 * @property int $id
 * @property string $first_name
 * @property string $second_name
 * @property string|null $third_name
 * @property string|null $passport_number
 * @property string|null $passport_pin
 * @property string|null $student_id_number
 * @property string $birth_date
 * @property string $dissertation_theme
 * @property string $home_address
 * @property string $accepted_date
 * @property string $_science_branch_id
 * @property int $_specialty_id
 * @property string $_payment_form
 * @property string $_citizenship
 * @property string $_nationality
 * @property string $_gender
 * @property string|null $_country
 * @property string|null $_province
 * @property string|null $_district
 * @property string $_doctoral_student_type
 * @property string $_doctorate_student_status
 * @property string $_level
 * @property int $_department
 * @property string|null $image
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EDissertationDefense[] $eDissertationDefenses
 * @property EDepartment $department
 * @property ESpecialty $specialty
 * @property CitizenshipType $citizenship
 * @property Country $country
 * @property DoctoralStudentType $doctoralStudentType
 * @property DoctorateStudentStatus $doctorateStudentStatus
 * @property Gender $gender
 * @property Nationality $nationality
 * @property PaymentForm $paymentForm
 * @property ScienceBranch $scienceBranch
 * @property Soato $province
 * @property Soato $district
 */
class EDoctorateStudent extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'create';

    protected $_translatedAttributes = [];
    public $scenario_edit_passport = false;
    // const ZERO_LEVEL = 10;
    const FIRST_LEVEL = 11;
    const SECOND_LEVEL = 12;
    const THIRD_LEVEL = 13;
    const VERSION = 1;

    public static function tableName()
    {
        return 'e_doctorate_student';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getLevelStatusOptions()
    {
        return [
            self::FIRST_LEVEL => __('1-bosqich'),
            self::SECOND_LEVEL => __('2-bosqich'),
            self::THIRD_LEVEL => __('3-bosqich'),
            //    self::ZERO_LEVEL => __('Tamomlagan'),
        ];
    }

    public static function getDoctorates()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            //->orderByTranslationField('name')
            ->all(), 'id', 'fullName');
    }

    public function rules()
    {
        $rules = array_merge(parent::rules(), [
            [['first_name', 'second_name', 'birth_date', 'dissertation_theme', 'home_address', 'accepted_date', '_science_branch_id', '_specialty_id', '_payment_form', '_citizenship', '_nationality', '_gender', '_doctoral_student_type', '_doctorate_student_status', '_department'], 'required', 'on' => self::SCENARIO_INSERT],
            [['birth_date', 'accepted_date', 'image', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['_specialty_id', '_department', 'position'], 'default', 'value' => null],
            [['_specialty_id', '_department', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['first_name', 'second_name', 'third_name'], 'string', 'max' => 100],
            [['passport_number', 'student_id_number'], 'string', 'max' => 14],
            [['passport_pin'], 'string', 'max' => 20],
            [['dissertation_theme'], 'string', 'max' => 500],
            [['home_address'], 'string', 'max' => 255],
            [['_science_branch_id'], 'string', 'max' => 36],
            [['_payment_form', '_citizenship', '_nationality', '_gender', '_country', '_province', '_district', '_doctoral_student_type', '_doctorate_student_status', '_level'], 'string', 'max' => 64],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_specialty_id'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty_id' => 'id']],
            [['_citizenship'], 'exist', 'skipOnError' => true, 'targetClass' => CitizenshipType::className(), 'targetAttribute' => ['_citizenship' => 'code']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
            [['_doctoral_student_type'], 'exist', 'skipOnError' => true, 'targetClass' => DoctoralStudentType::className(), 'targetAttribute' => ['_doctoral_student_type' => 'code']],
            [['_doctorate_student_status'], 'exist', 'skipOnError' => true, 'targetClass' => DoctorateStudentStatus::className(), 'targetAttribute' => ['_doctorate_student_status' => 'code']],
            [['_gender'], 'exist', 'skipOnError' => true, 'targetClass' => Gender::className(), 'targetAttribute' => ['_gender' => 'code']],
            [['_nationality'], 'exist', 'skipOnError' => true, 'targetClass' => Nationality::className(), 'targetAttribute' => ['_nationality' => 'code']],
            [['_payment_form'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentForm::className(), 'targetAttribute' => ['_payment_form' => 'code']],
            [['_science_branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScienceBranch::className(), 'targetAttribute' => ['_science_branch_id' => 'id']],
            [['_province'], 'exist', 'skipOnError' => true, 'targetClass' => Soato::className(), 'targetAttribute' => ['_province' => 'code']],
            [['_district'], 'exist', 'skipOnError' => true, 'targetClass' => Soato::className(), 'targetAttribute' => ['_district' => 'code']],
        ]);
        if ($this->scenario_edit_passport) {
            $rules = array_merge($rules, [
                [['passport_number', '_citizenship', 'birth_date'], 'required', 'on' => self::SCENARIO_INSERT],
                [['_gender', '_nationality'], 'safe', 'on' => self::SCENARIO_INSERT]
            ]);
        }
        if ($this->student_id_number == null) {
            $rules = array_merge($rules, [
                [['passport_number', '_citizenship', 'birth_date'], 'required', 'on' => self::SCENARIO_INSERT],
                [['passport_number'], 'string', 'max' => 15],
                [['passport_pin'], 'string', 'max' => 20],
                [['passport_number', 'passport_pin'], 'unique'],

                [['passport_pin'], 'required', 'when' => function () {
                    return $this->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_UZB || $this->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_NOTCITIZENSHIP;
                }, 'whenClient' => 'checkCitizenship', 'on' => self::SCENARIO_INSERT],
            ]);
        }
        return $rules;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_specialty_id' => __('Doctorate Specialty'),
        ]);
    }

    public function getEDissertationDefenses()
    {
        return $this->hasMany(EDissertationDefense::className(), ['_doctorate_student' => 'id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty_id']);
    }

    public function getCitizenship()
    {
        return $this->hasOne(CitizenshipType::className(), ['code' => '_citizenship']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getDoctoralStudentType()
    {
        return $this->hasOne(DoctoralStudentType::className(), ['code' => '_doctoral_student_type']);
    }

    public function getDoctorateStudentStatus()
    {
        return $this->hasOne(DoctorateStudentStatus::className(), ['code' => '_doctorate_student_status']);
    }

    public function getGender()
    {
        return $this->hasOne(Gender::className(), ['code' => '_gender']);
    }

    public function getNationality()
    {
        return $this->hasOne(Nationality::className(), ['code' => '_nationality']);
    }

    public function getPaymentForm()
    {
        return $this->hasOne(PaymentForm::className(), ['code' => '_payment_form']);
    }

    public function getScienceBranch()
    {
        return $this->hasOne(ScienceBranch::className(), ['id' => '_science_branch_id']);
    }

    public function getProvince()
    {
        return $this->hasOne(Soato::className(), ['code' => '_province']);
    }

    public function getDistrict()
    {
        return $this->hasOne(Soato::className(), ['code' => '_district']);
    }

    public function getFullName()
    {
        return strtoupper(trim($this->second_name . ' ' . $this->first_name . ' ' . $this->third_name));
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
                'attributes' => [
                    'second_name' => [
                        SORT_ASC => ['second_name' => SORT_ASC, 'first_name' => SORT_ASC, 'third_name' => SORT_ASC],
                        SORT_DESC => ['second_name' => SORT_DESC, 'first_name' => SORT_DESC, 'third_name' => SORT_DESC],
                    ],
                    'id',
                    'student_id_number',
                    'position',
                    '_doctoral_student_type',
                    '_specialty_id',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);
        if ($this->search) {
            $query->orWhereLike('first_name', $this->search);
            $query->orWhereLike('second_name', $this->search);
            $query->orWhereLike('third_name', $this->search);
            $query->orWhereLike('passport_number', $this->search);
            $query->orWhereLike('passport_pin', $this->search);
            $query->orWhereLike('student_id_number', $this->search);
        }

        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_doctoral_student_type) {
            $query->andFilterWhere(['_doctoral_student_type' => $this->_doctoral_student_type]);
        }
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return $this->getFullName();
    }


}
