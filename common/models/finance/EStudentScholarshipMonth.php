<?php

namespace common\models\finance;

use common\models\curriculum\EducationYear;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\StipendRate;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_scholarship_month".
 *
 * @property int $id
 * @property int $_student
 * @property int $_student_scholarship
 * @property string $_stipend_rate
 * @property string|null $_education_year
 * @property string $_semester
 * @property string $month_name
 * @property float $summa
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EducationYear $educationYear
 * @property EStudent $student
 * @property EStudentScholarship $studentScholarship
 * @property StipendRate $stipendRate
 */
class EStudentScholarshipMonth extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    public static function tableName()
    {
        return 'e_student_scholarship_month';
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
            [['month_name', 'summa'], 'required', 'on' => self::SCENARIO_CREATE],
            //[['_student', '_student_scholarship', '_stipend_rate', '_semester', 'month_name', 'summa'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_student', '_student_scholarship', 'position'], 'default', 'value' => null],
            [['_student', '_student_scholarship', 'position'], 'integer'],
            [['month_name', 'updated_at', 'created_at'], 'safe'],
            [['summa'], 'number'],
            [['active'], 'boolean'],
            [['_stipend_rate', '_education_year', '_semester'], 'string', 'max' => 64],
            [['_student', '_semester', '_education_year', 'month_name'], 'unique', 'targetAttribute' => ['_student', '_semester', '_education_year', 'month_name'], 'message'=>__('A scholarship has been assigned for this month')],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_student_scholarship'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentScholarship::className(), 'targetAttribute' => ['_student_scholarship' => 'id']],
            [['_stipend_rate'], 'exist', 'skipOnError' => true, 'targetClass' => StipendRate::className(), 'targetAttribute' => ['_stipend_rate' => 'code']],
        ]);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getStudentScholarship()
    {
        return $this->hasOne(EStudentScholarship::className(), ['id' => '_student_scholarship']);
    }

    public function getStipendRate()
    {
        return $this->hasOne(StipendRate::className(), ['code' => '_stipend_rate']);
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
                    '_student',
                    '_student_scholarship',
                    '_semester',
                    '_education_year',
                    '_stipend_rate',
                    'summa',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        if ($this->_student_scholarship) {
            $query->andFilterWhere(['_student_scholarship' => $this->_student_scholarship]);
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

        return $dataProvider;
    }
}
