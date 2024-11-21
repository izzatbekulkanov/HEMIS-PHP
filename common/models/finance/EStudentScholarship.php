<?php

namespace common\models\finance;

use common\models\academic\EDecree;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\StipendRate;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_scholarship".
 *
 * @property int $id
 * @property int $_department
 * @property int $_specialty
 * @property string $_education_type
 * @property string $_education_form
 * @property int $_curriculum
 * @property int $_group
 * @property string $_payment_form
 * @property int $_student
 * @property string $_semester
 * @property string|null $_education_year
 * @property string $_stipend_rate
 * @property int|null $_decree
 * @property float $summa
 * @property string $start_date
 * @property string $end_date
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EDecree $decree
 * @property EDepartment $department
 * @property EducationYear $educationYear
 * @property EGroup $group
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EducationForm $educationForm
 * @property EducationType $educationType
 * @property PaymentForm $paymentForm
 * @property StipendRate $stipendRate
 */
class EStudentScholarship extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    public static function tableName()
    {
        return 'e_student_scholarship';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getScholarByStudent($_student= false, $_semester = false, $_education_year = false)
    {
        $result="";
        $result = self::find()
            ->where([
                //'active' => self::STATUS_ENABLE,
                '_student'=>$_student,
                '_semester'=>$_semester,
                '_education_year'=>$_education_year,
            ])
            ->one();

        return $result;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_department', '_specialty', '_education_type', '_education_form', '_curriculum', '_group', '_payment_form', '_student', '_semester', '_stipend_rate', 'summa', 'start_date', 'end_date'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_department', '_specialty', '_curriculum', '_group', '_student', '_decree', 'position'], 'default', 'value' => null],
            [['_department', '_specialty', '_curriculum', '_group', '_student', '_decree', 'position'], 'integer'],
            [['summa'], 'number'],
            [['start_date', 'end_date', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_type', '_education_form', '_payment_form', '_semester', '_education_year', '_stipend_rate'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_decree'], 'exist', 'skipOnError' => true, 'targetClass' => EDecree::className(), 'targetAttribute' => ['_decree' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_payment_form'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentForm::className(), 'targetAttribute' => ['_payment_form' => 'code']],
            [['_stipend_rate'], 'exist', 'skipOnError' => true, 'targetClass' => StipendRate::className(), 'targetAttribute' => ['_stipend_rate' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_department' => __('Faculty'),
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getDecree()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getPaymentForm()
    {
        return $this->hasOne(PaymentForm::className(), ['code' => '_payment_form']);
    }

    public function getStipendRate()
    {
        return $this->hasOne(StipendRate::className(), ['code' => '_stipend_rate']);
    }

    public function getScholarshipMonth()
    {
        return $this->hasMany(EStudentScholarshipMonth::class, ['_student_scholarship' => 'id']);
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
                    '_department',
                    '_specialty',
                    '_education_type',
                    '_education_form',
                    '_curriculum',
                    '_group',
                    '_payment_form',
                    '_student',
                    '_semester',
                    '_education_year',
                    '_stipend_rate',
                    '_decree',
                    'summa',
                    'start_date',
                    'end_date',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['_payment_form' => $this->_payment_form]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_stipend_rate) {
            $query->andFilterWhere(['_stipend_rate' => $this->_stipend_rate]);
        }
        if ($this->_decree) {
            $query->andFilterWhere(['_decree' => $this->_decree]);
        }
        return $dataProvider;
    }
}
