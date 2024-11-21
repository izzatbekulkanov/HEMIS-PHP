<?php

namespace common\models\student;

use common\components\Config;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\HemisApi;
use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\archive\EAcademicRecord;
use common\models\archive\EGraduateQualifyingWork;
use common\models\curriculum\EStudentSubject;
use common\models\performance\EPerformance;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\Accommodation;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Country;
use common\models\system\classifier\Gender;
use common\models\system\classifier\Nationality;
use common\models\system\classifier\Region;
use common\models\system\classifier\Soato;
use common\models\system\classifier\SocialCategory;
use common\models\system\classifier\StudentLivingStatus;
use common\models\system\classifier\StudentRoommateType;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\StudentType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student".
 *
 * @property int $id
 * @property string $first_name
 * @property string $second_name
 * @property string|null $third_name
 * @property string $phone
 * @property string $birth_date
 * @property string $_gender
 * @property string $_uid
 * @property string $_sync
 * @property string $_nationality
 * @property string $_citizenship
 * @property string $_decree_enroll
 * @property string $passport_number
 * @property string $passport_pin
 * @property string|null $student_id_number
 * @property string $_country
 * @property string $_province
 * @property string $_current_province
 * @property string $_district
 * @property string $_current_district
 * @property string $home_address
 * @property string $current_address
 * @property string $password
 * @property string $auth_key
 * @property string $password_reset_token
 * @property string $access_token
 * @property DateTime $password_reset_date
 * @property int $year_of_enter
 * @property string $_accommodation
 * @property string $_student_type
 * @property string $person_phone
 * @property string $parent_phone
 * @property string $email
 * @property string $geo_location
 * @property integer $roommate_count
 * @property string $_student_living_status
 * @property string $_student_roommate_type
 * @property string|null $other
 * @property string|null $photo
 * @property int|null $position
 * @property bool|null $active
 * @property boolean $password_valid
 * @property DateTime $password_date
 * @property Country $citizenship
 * @property Country $country
 * @property Nationality $nationality
 * @property Gender $gender
 * @property Region $province
 * @property Region $currentProvince
 * @property Accommodation $accommodation
 * @property StudentType $student_type
 * @property Region $district
 * @property Region $currentDistrict
 * @property SocialCategory $socialCategory
 * @property EGroup[] $groups
 * @property EAcademicRecord[] $academicRecords
 * @property EStudentMeta $meta
 * @property EGraduateQualifyingWork $graduateWork
 * @property EDecree $decreeEnroll
 * @property StudentLivingStatus $studentLivingStatus
 * @property StudentRoommateType $studentRoommateType
 */
class EStudent extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SCENARIO_INSERT_UZASBO = 'register_uzasbo';
    const STATUS_REGISTRATION_ON = 1;
    const STATUS_REGISTRATION_OFF = 0;
    const PASSWORD_VALIDATOR = '/^(?=.*\d)(?=.*[A-Za-z]).{8,}$/';

    protected $_translatedAttributes = ['first_name', 'second_name', 'other'];
    protected $_searchableAttributes = ['first_name', 'second_name', 'third_name', 'student_id_number', 'passport_pin', 'passport_number'];
    public $change_password = false;
    public $confirmation;
    public $scenario_passport_edit = false;

    //public $_social_category;

    public static function tableName()
    {
        return 'e_student';
    }

    public static function getYearOfEnterOptions()
    {
        $years = [];

        for ($i = date('Y'); $i > (date('Y') - 31); $i--)
            $years [$i] = $i;

        return $years;
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getRegistrationOptions()
    {
        return [
            self::STATUS_REGISTRATION_ON => __('Uzasbo Id Number Set'),
            self::STATUS_REGISTRATION_OFF => __('Uzasbo Id Number Not Set'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->active]) ? $labels[$this->active] : '';
    }

    public static function getStudents()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            //->orderByTranslationField('name')
            ->all(), 'id', 'fullName');
    }

    public function rules()
    {
        $rules = array_merge(parent::rules(), [
            [['first_name', 'second_name', '_gender', '_nationality', '_country', '_province', '_district', 'home_address', '_social_category', 'year_of_enter', '_accommodation', 'other', '_student_type'], 'required', 'on' => self::SCENARIO_INSERT],
            [['uzasbo_id_number'], 'required', 'on' => self::SCENARIO_INSERT_UZASBO],
            [['birth_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['year_of_enter', 'position'], 'default', 'value' => null],
            [['year_of_enter', 'position', 'roommate_count', '_decree_enroll'], 'integer'],
            [['active'], 'boolean'],
            [['email'], 'email'],
            [['first_name', 'second_name', 'third_name'], 'string', 'max' => 100],
            [['_gender', '_nationality', '_citizenship', '_country', '_province', '_district', '_accommodation', '_social_category', '_student_type'], 'string', 'max' => 64],


            [['student_id_number'], 'string', 'max' => 14],
            [['home_address', 'current_address'], 'string', 'max' => 255],
            [['home_address', 'current_address'], 'match', 'pattern' => '/^[A-Za-z\ 0-9\(\)\,\.\-\_\"\'‘’`\/]+$/i', 'message' => __('Manzil ma\'lumotlari lotinda kiritilsin')],
            //[['first_name', 'second_name', 'third_name'], 'match', 'pattern' => '/^[A-Za-z\ \-\'‘’`]+$/i', 'message' => __('Ism-familiya lotinda kiritilsin')],
            [['other'], 'string', 'max' => 1024],
            [['geo_location'], 'string', 'max' => 2000],
            [['_current_province', '_current_district', '_student_roommate_type', '_student_living_status'], 'string', 'max' => 64],
            [['image', 'geo_location'], 'safe'],
            ['uzasbo_id_number', 'unique', 'message' => __('Uzasbo Id Number {value} was created already')],


            [['_citizenship'], 'exist', 'skipOnError' => true, 'targetClass' => CitizenshipType::className(), 'targetAttribute' => ['_citizenship' => 'code']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
            [['_nationality'], 'exist', 'skipOnError' => true, 'targetClass' => Nationality::className(), 'targetAttribute' => ['_nationality' => 'code']],
            [['_province', '_current_province'], 'exist', 'skipOnError' => true, 'targetClass' => Soato::className(), 'targetAttribute' => ['_province' => 'code']],
            [['_district', '_current_district'], 'exist', 'skipOnError' => true, 'targetClass' => Soato::className(), 'targetAttribute' => ['_district' => 'code']],
            [['_accommodation'], 'exist', 'skipOnError' => true, 'targetClass' => Accommodation::className(), 'targetAttribute' => ['_accommodation' => 'code']],
            [['_student_type'], 'exist', 'skipOnError' => true, 'targetClass' => StudentType::className(), 'targetAttribute' => ['_student_type' => 'code']],
            [['_social_category'], 'exist', 'skipOnError' => true, 'targetClass' => SocialCategory::className(), 'targetAttribute' => ['_social_category' => 'code']],
            [['_student_roommate_type'], 'exist', 'skipOnError' => true, 'targetClass' => StudentRoommateType::className(), 'targetAttribute' => ['_student_roommate_type' => 'code']],
            [['_student_living_status'], 'exist', 'skipOnError' => true, 'targetClass' => StudentLivingStatus::className(), 'targetAttribute' => ['_student_living_status' => 'code']],
            [['_decree_enroll'], 'exist', 'skipOnError' => true, 'targetClass' => EDecree::className(), 'targetAttribute' => ['_decree_enroll' => 'id']],

            [['change_password'], 'safe', 'on' => ['profile']],

            [['password', 'confirmation'], 'required', 'on' => ['profile'], 'when' => function ($model) {
                return $model->change_password == 1;
            }, 'whenClient' => "function (attribute, value) {return $('#change_password').is(':checked');}"],

            [['confirmation'], 'compare', 'on' => ['profile'], 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => __('Confirmation does not match'), 'when' => function ($model) {
                return $model->change_password == 1;
            }],

            [['phone', 'person_phone', 'parent_phone'], 'match', 'pattern' => '/^[\+\(]{0,2}[998]{0,3}[\)]{0,1}[ ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{3}[- ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{2}$/', 'message' => __('Wrong mobile phone number')],

            [['passport_number', 'passport_pin'], 'unique', 'on' => self::SCENARIO_INSERT, 'filter' => function (ActiveQuery $query) {
                $query->andWhere(['year_of_enter' => $this->year_of_enter]);
            }],

            [['passport_pin'], 'validateStudentStatus', 'when' => function () {
                return $this->isNewRecord;
            }]
        ]);

        if ($this->scenario_passport_edit) {
            $rules = array_merge($rules, [
                [['passport_number', '_citizenship', 'birth_date', '_gender', '_nationality'], 'required', 'on' => self::SCENARIO_INSERT],
                [['_gender', '_nationality'], 'safe', 'on' => self::SCENARIO_INSERT]
            ]);
        }

        if ($this->student_id_number == null) {
            $rules = array_merge($rules, [
                [['passport_number', '_citizenship', 'birth_date'], 'required', 'on' => self::SCENARIO_INSERT],
                [['passport_number'], 'string', 'max' => 15],
                [['passport_pin'], 'string', 'max' => 20],

                [['passport_pin'], 'required', 'when' => function () {
                    return $this->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_UZB || $this->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_NOTCITIZENSHIP;
                }, 'whenClient' => 'checkCitizenship', 'on' => self::SCENARIO_INSERT],
            ]);
        }
        return $rules;
    }

    public function validateStudentStatus($attribute, $options)
    {
        $student = self::find()
            ->where([
                'passport_pin' => $this->passport_pin
            ])
            ->leftJoin('e_student_meta', 'e_student.id=e_student_meta._student and e_student_meta.active=true')
            ->andWhere(['not in', 'e_student_meta._student_status', [StudentStatus::STUDENT_TYPE_GRADUATED, StudentStatus::STUDENT_TYPE_EXPEL]])
            ->one();

        if ($student) {
            $this->addError($attribute, __('Ushbu {pin} PNFL raqamga ega hamda {status} statusga ega talaba mavjud', [
                'pin' => $this->passport_pin,
                'status' => $student->meta->studentStatus->name,
            ]));
        }
    }

    public function getCitizenshipType()
    {
        return $this->hasOne(CitizenshipType::className(), ['code' => '_citizenship']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getStudentLivingStatus()
    {
        return $this->hasOne(StudentLivingStatus::className(), ['code' => '_student_living_status']);
    }

    public function getStudentRoommateType()
    {
        return $this->hasOne(StudentRoommateType::className(), ['code' => '_student_roommate_type']);
    }

    public function getDecreeEnroll()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree_enroll']);
    }

    public function getCitizenship()
    {
        return $this->hasOne(CitizenshipType::className(), ['code' => '_citizenship']);
    }

    public function getGender()
    {
        return $this->hasOne(Gender::className(), ['code' => '_gender']);
    }

    public function getNationality()
    {
        return $this->hasOne(Nationality::className(), ['code' => '_nationality']);
    }

    public function getProvince()
    {
        return $this->hasOne(Soato::className(), ['code' => '_province']);
    }

    public function getDistrict()
    {
        return $this->hasOne(Soato::className(), ['code' => '_district']);
    }

    public function getCurrentProvince()
    {
        return $this->hasOne(Soato::className(), ['code' => '_current_province']);
    }

    public function getCurrentDistrict()
    {
        return $this->hasOne(Soato::className(), ['code' => '_current_district']);
    }

    public function getAccommodation()
    {
        return $this->hasOne(Accommodation::className(), ['code' => '_accommodation']);
    }

    public function getStudentType()
    {
        return $this->hasOne(StudentType::className(), ['code' => '_student_type']);
    }

    public function getSocialCategory()
    {
        return $this->hasOne(SocialCategory::className(), ['code' => '_social_category']);
    }


    public function getMeta()
    {
        /**
         * @todo select actual meta
         */
        return $this->hasOne(EStudentMeta::className(), ['_student' => 'id'])->where(['e_student_meta.active' => true])->with(['educationForm', 'educationType']);
    }

    public function getStudentMeta()
    {
        /**
         * @todo select studied meta
         */
        return $this->hasOne(EStudentMeta::className(), ['_student' => 'id'])->where(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
    }

    public function getPerformances()
    {
        return $this->hasMany(EPerformance::class, ['_student' => 'id']);
    }

    public function getAcademicRecords()
    {
        return $this->hasMany(EAcademicRecord::class, ['_student' => 'id'])->where(['e_academic_record.active' => true]);
    }

    /**
     * @param $subjectId
     * @return array|EAcademicRecord|null
     */
    public function getSubjectRecord($subjectId)
    {
        return $this->getAcademicRecords()->andFilterWhere(['_subject' => $subjectId])->one();
    }

    public function getGroups()
    {
        return $this->hasMany(EGroup::className(), ['id' => '_group'])
            ->viaTable('e_student_meta', ['_student' => 'id'], function ($query) {
                $query->andFilterWhere(['active' => true]);
            });
    }

    public function getSubjects()
    {
        return $this->hasMany(EStudentSubject::class, ['_student' => 'id'])->where(['active' => true]);
    }

    public function getGroupIds()
    {
        return $this->getGroups()->select('id')->column();
    }

    public function getGraduateWork()
    {
        return $this->hasOne(EGraduateQualifyingWork::class, ['_student' => 'id'])->where(['active' => true]);
    }

    /*public function getSystemLog()
    {
        return SystemLog::find()
            ->where(['e_system_log._student'=>$this->id])
            ->orderBy(['e_system_log.created_at'=>SORT_DESC])
            ->one();
    }*/

    public function beforeSave($insert)
    {
        if (empty($this->password)) {
            $this->setPassword($this->passport_number);
        }
        foreach (['first_name', 'second_name', 'third_name'] as $att) {
            $this->$att = mb_strtoupper(strip_tags($this->$att));
        }
        foreach (['_current_province', '_current_district', 'current_address', '_accommodation'] as $att) {
            $this->$att = strip_tags($this->$att);
        }

        if ($this->change_password) {
            $this->setPassword($this->confirmation);
        }

        if (empty($this->passport_pin)) $this->passport_pin = null;

        if ($this->isAttributeChanged('_decree_enroll', false)) {
            if ($did = $this->getOldAttribute('_decree_enroll')) {
                EDecreeStudent::deleteAll(['_decree' => $did, '_student' => $this->id]);
            }
        }

        if (!$this->_current_province) $this->_current_province = null;
        if (!$this->_current_district) $this->_current_district = null;
        if (!$this->_student_living_status) $this->_student_living_status = null;
        if (!$this->_student_roommate_type) $this->_student_roommate_type = null;

        foreach (['phone', 'parent_phone', 'person_phone'] as $att) {
            if ($this->$att) {
                $this->$att = self::normalizeMobile($this->$att);
            }
        }


        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->_decree_enroll)
            $this->decreeEnroll->registerStudent($this, \Yii::$app->user->identity);

        parent::afterSave($insert, $changedAttributes);
    }

    public function setPassword($password)
    {
        if ($this->hasAttribute('password_date')) {
            $this->password_date = $this->getTimestampValue();
            $this->password_valid = true;
        }

        $this->password = Yii::$app->security->generatePasswordHash($password);
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->access_token = Yii::$app->security->generateRandomString();
        $this->password_reset_token = null;
        $this->password_reset_date = null;
    }

    public static function getLoginIdAttribute()
    {
        return HEMIS_INTEGRATION && !(boolean)Config::get("disable_sync_model_" . self::class) ? 'student_id_number' : 'passport_number';
    }

    public function getFullName()
    {
        if (Yii::$app->language === Config::LANGUAGE_ENGLISH) {
            return strtoupper(trim(@$this->second_name . ' ' . @$this->first_name));
        }
        return strtoupper(trim($this->second_name . ' ' . $this->first_name . ' ' . $this->third_name));
    }


    public function getShortName()
    {
        return trim($this->second_name . ' ' . @mb_substr($this->first_name, 0, 1) . '. ' . @mb_substr($this->third_name, 0, 1) . '.');
    }

    public function searchByFaculty(Admin $admin, $params, $hasProblem = false)
    {
        $this->load($params);

        $query = self::find()
            ->with(['meta'])
            ->leftJoin('e_student_meta', 'e_student.id=e_student_meta._student and e_student_meta.active=true');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
                'attributes' => [
                    'second_name' => [
                        SORT_ASC => ['second_name' => SORT_ASC, 'first_name' => SORT_ASC, 'third_name' => SORT_ASC],
                        SORT_DESC => ['second_name' => SORT_DESC, 'first_name' => SORT_DESC, 'third_name' => SORT_DESC],
                    ],
                    'id',
                    'student_id_number',
                    'position',
                    'passport_number',
                    'year_of_enter',
                    'updated_at',
                    'created_at',
                    'active',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('first_name', $this->search);
            $query->orWhereLike('second_name', $this->search);
            $query->orWhereLike('third_name', $this->search);
            $query->orWhereLike('passport_number', $this->search);
            $query->orWhereLike('passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        if ($this->year_of_enter) {
            $query->andFilterWhere(['year_of_enter' => $this->year_of_enter]);
        }

        if ($admin->role->code == AdminRole::CODE_DEAN) {
            if ($admin->employee && $admin->employee->deanFaculties) {
                $query->andWhere(new Expression('e_student_meta._department=:department OR e_student_meta._department is NULL '), ['department' => $admin->employee->deanFaculties->id]);
            } else {

            }
        }

        if ($hasProblem) {
            $query->andWhere(new Expression('e_student_meta._department is NULL OR e_student_meta._group is NULL OR e_student.student_id_number is NULL OR e_student._sync_status!=\'actual\' '), [
            ]);
        }

        return $dataProvider;
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()
            ->leftJoin('e_student_meta', 'e_student.id=e_student_meta._student');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
                'attributes' => [
                    'second_name' => [
                        SORT_ASC => ['second_name' => SORT_ASC, 'first_name' => SORT_ASC, 'third_name' => SORT_ASC],
                        SORT_DESC => ['second_name' => SORT_DESC, 'first_name' => SORT_DESC, 'third_name' => SORT_DESC],
                    ],
                    'id',
                    'student_id_number',
                    'position',
                    'passport_number',
                    'year_of_enter',
                    'updated_at',
                    'created_at',
                    'active',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('first_name', $this->search);
            $query->orWhereLike('second_name', $this->search);
            $query->orWhereLike('third_name', $this->search);
            $query->orWhereLike('passport_number', $this->search);
            $query->orWhereLike('passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        if ($this->_sync_status) {
            $query->andFilterWhere(['e_student._sync_status' => $this->_sync_status]);
        }
        return $dataProvider;
    }

    public static function setSyncRequiredByGroup(EGroup $group)
    {
        $students = EStudentMeta::find()
            ->select(['_student'])
            ->where(['_group' => $group->id])
            ->column();

        if (count($students)) {
            self::updateAll(['_sync' => false], ['id' => $students]);
        }
    }

    public function getDescriptionForSync()
    {
        return $this->getFullName();
    }

    public static function normalizeMobile($number, $nums = -9, $prefix = '+998')
    {
        return $prefix . substr(preg_replace('/\D/', '', $number), $nums);
    }
}
