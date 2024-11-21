<?php

namespace common\models\performance;



use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\student\EGroup;
use common\models\system\_BaseModel;
use common\models\system\classifier\ExamType;
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
use Imagine\Image\ManipulatorInterface;

/**
 * This is the model class for table "e_performance_control".
 *
 * @property int $id
 * @property int $_exam_schedule
 * @property int $_group
 * @property string $_education_year
 * @property string $_semester
 * @property int $_subject
 * @property int $_employee
 * @property string $_lesson_pair
 * @property string $_exam_type
 * @property string $exam_name
 * @property string $exam_date
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEmployee $employee
 * @property EGroup $group
 * @property ESubject $subject
 * @property ESubjectExamSchedule $examSchedule
 * @property HEducationYear $educationYear
 * @property HExamType $examType
 */
class EPerformanceControl extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_performance_control';
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
            [['_exam_schedule', '_group', '_education_year', '_semester', '_subject', '_employee', '_lesson_pair', '_exam_type', 'exam_name', 'exam_date', 'updated_at', 'created_at'], 'required'],
            [['_exam_schedule', '_group', '_subject', '_employee'], 'default', 'value' => null],
            [['_exam_schedule', '_group', '_subject', '_employee'], 'integer'],
            [['exam_date', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_year', '_semester', '_lesson_pair', '_exam_type', 'exam_name'], 'string', 'max' => 64],
            [['_employee', '_group', '_education_year', '_semester', '_subject', '_exam_type', 'exam_name', '_lesson_pair', 'exam_date'], 'unique', 'targetAttribute' => ['_employee', '_group', '_education_year', '_semester', '_subject', '_exam_type', 'exam_name', '_lesson_pair', 'exam_date']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_exam_schedule'], 'exist', 'skipOnError' => true, 'targetClass' => ESubjectExamSchedule::className(), 'targetAttribute' => ['_exam_schedule' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => ExamType::className(), 'targetAttribute' => ['_exam_type' => 'code']],
        ]);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getExamSchedule()
    {
        return $this->hasOne(ESubjectExamSchedule::className(), ['id' => '_exam_schedule']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getExamType()
    {
        return $this->hasOne(ExamType::className(), ['code' => '_exam_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_education_year',
                    '_semester',
                    'exam_date',
                    '_subject',
                    '_group',
                    '_exam_type',
                    '_lesson_pair',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        return $dataProvider;
    }
}
