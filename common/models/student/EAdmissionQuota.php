<?php

namespace common\models\student;

use common\models\curriculum\EducationYear;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_admission_quota".
 *
 * @property int $id
 * @property int $_specialty_id
 * @property string $_education_type
 * @property string|null $_education_form
 * @property string $_specialty
 * @property string $_education_year
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ESpecialty $specialty
 * @property EducationForm $educationForm
 * @property EducationYear $educationYear
 * @property EducationType $educationType
 */
class EAdmissionQuota extends _BaseModel
{

    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_ORDER = 'order';
    const STATUS_REGISTRATION_ON = 1;
    const STATUS_REGISTRATION_OFF = 0;

    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return 'e_admission_quota';
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

    public static function getRegistrationOptions()
    {
        return [
            self::STATUS_REGISTRATION_ON => __('Set'),
            self::STATUS_REGISTRATION_OFF => __('Not Set'),
        ];
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        '_education_type',
                        '_specialty',
                        '_education_year',
                        '_education_form',
                        'admission_quota',
                        '_quota_type',
                    ],
                    'required',
                    'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE],
                ],
                [['active'], 'boolean'],
                [['admission_quota'], 'integer'],
                [['_translations', 'updated_at', 'created_at'], 'safe'],
                [
                    [
                        '_education_type',
                        '_education_form',
                        '_education_year',
                    ],
                    'string',
                    'max' => 64,
                ],
                [
                    ['_specialty'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESpecialty::className(),
                    'targetAttribute' => ['_specialty' => 'id'],
                ],
                [
                    ['_education_form'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationForm::className(),
                    'targetAttribute' => ['_education_form' => 'code'],
                ],
                [
                    ['_education_type'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationType::className(),
                    'targetAttribute' => ['_education_type' => 'code'],
                ],
                [
                    ['_education_year'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationYear::className(),
                    'targetAttribute' => ['_education_year' => 'code'],
                ],
                [
                    ['_quota_type'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => PaymentForm::className(),
                    'targetAttribute' => ['_quota_type' => 'code'],
                ],
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_specialty' => __('Specialty'),
            ]
        );
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getStudents()
    {
        return $this->hasMany(
            EStudentMeta::class,
            [
                '_specialty_id' => '_specialty',
                '_education_type' => '_education_type',
                '_education_form' => '_education_form',
                '_education_year' => '_education_year',
                '_payment_form' => '_quota_type'
            ]
        )->andFilterWhere(['e_student_meta.active' => true]);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getQuotaType()
    {
        return $this->hasOne(PaymentForm::className(), ['code' => '_quota_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }


    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_specialty_id',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }

        return $dataProvider;
    }

    public function searchForMonitoring($params)
    {
        $this->load($params);

        $query = self::find();
        //$query->joinWith(['student']);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_specialty_id',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }

        return $dataProvider;
    }

}
