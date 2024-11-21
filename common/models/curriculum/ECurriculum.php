<?php

namespace common\models\curriculum;

use common\models\student\EGroup;
use common\models\student\EQualification;
use common\models\system\_BaseModel;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationForm;
use common\models\curriculum\EducationYear;
use common\models\curriculum\MarkingSystem;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
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
use yii\helpers\StringHelper;

/**
 * This is the model class for table "e_curriculum".
 *
 * @property int $id
 * @property string $name
 * @property int $_department
 * @property string $_education_type
 * @property string $_education_form
 * @property string $_specialty_id
 * @property string $_marking_system
 * @property string $_education_year
 * @property int|null $position
 * @property bool|null $active
 * @property bool|null $accepted
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EDepartment $department
 * @property ESpecialty $specialty
 * @property Semester $lastSemester
 * @property Semester[] $semesters
 * @property HEducationForm $educationForm
 * @property HEducationType $educationType
 * @property HEducationYear $educationYear
 * @property MarkingSystem $markingSystem
 * @property EGroup[] $eGroups
 */
class ECurriculum extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DEAN_CREATE = 'dean_create';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_curriculum';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getAcceptedOptions()
    {
        return [
            self::STATUS_ENABLE => __('Accepted'),
            self::STATUS_DISABLE => __('Is not accepted'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->active]) ? $labels[$this->active] : '';
    }

    public function getAcceptedLabel()
    {
        $labels = self::getAcceptedOptions();
        return isset($labels[$this->accepted]) ? $labels[$this->accepted] : '';
    }

    public static function getOptions($faculty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE, '_department' => $faculty])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }
        return $result;
    }

    public static function getFullOptions($faculty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->orderByTranslationField('name')
                ->all(), 'id', 'fullName');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE, '_department' => $faculty])
                ->orderByTranslationField('name')
                ->all(), 'id', 'fullName');
        }
        return $result;
    }

    public static function getOptionsByEduTypeForm($education_type = "", $education_form = "", $faculty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['_education_type' => $education_type, '_education_form' => $education_form])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['_education_type' => $education_type, '_education_form' => $education_form, '_department' => $faculty])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }
        return $result;
    }

    public static function getOptionsByEduTypeFormSpec($education_type = "", $education_form = "", $faculty = "", $specialty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['_education_type' => $education_type, '_education_form' => $education_form, '_specialty_id' => $specialty])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['_education_type' => $education_type, '_education_form' => $education_form, '_department' => $faculty, '_specialty_id' => $specialty])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }
        return $result;
    }

    public static function getAcceptStatus()
    {
        $result = self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->orderByTranslationField('name')
            ->all();
        $accept_status = array();
        foreach ($result as $item) {
            $accept_status[$item->id] = ['disabled' => !$item->accepted];
        }
        return $accept_status;
    }

    public static function getOptionsByEduForm($education_form = "", $faculty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['_education_form' => $education_form])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['_education_form' => $education_form, '_department' => $faculty])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }
        return $result;
    }

    public static function getOptionsByDepartmentSpecialty($faculty = "", $specialty = "")
    {
        $result = ArrayHelper::map(self::find()
            ->where(['_department' => $faculty, '_specialty_id' => $specialty])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
        return $result;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', '_department', '_education_form', '_specialty_id', '_qualification', '_marking_system', '_education_year', 'semester_count', 'education_period', 'autumn_start_date', 'autumn_end_date', 'spring_start_date', 'spring_end_date'], 'required', 'on' => self::SCENARIO_CREATE],
            [['name', '_education_form', '_specialty_id', '_qualification', '_marking_system', '_education_year', 'semester_count', 'education_period', 'autumn_start_date', 'autumn_end_date', 'spring_start_date', 'spring_end_date'], 'required', 'on' => self::SCENARIO_DEAN_CREATE],
            [['_department', 'position'], 'default', 'value' => null],
            [['_department', 'position', 'semester_count', 'education_period', '_specialty_id'], 'integer'],
            [['active', 'accepted'], 'boolean'],
            [['_translations', 'updated_at', 'created_at', 'autumn_start_date', 'autumn_end_date', 'spring_start_date', 'spring_end_date'], 'safe'],
            [['name'], 'string', 'max' => 256],
            [['_education_type', '_education_form', '_marking_system', '_education_year'], 'string', 'max' => 64],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_specialty_id'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty_id' => 'id']],
            [['_qualification'], 'exist', 'skipOnError' => true, 'targetClass' => EQualification::className(), 'targetAttribute' => ['_qualification' => 'id']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_marking_system'], 'exist', 'skipOnError' => true, 'targetClass' => MarkingSystem::className(), 'targetAttribute' => ['_marking_system' => 'code']],
        ]);
    }

    public function getFullName()
    {
        $accepted = "";
        $labels = self::getAcceptedOptions();
        if ($this->accepted)
            $accepted = $this->name;
        else
            $accepted = $this->name . ' (' . $labels[$this->accepted] . ')';
        return $accepted;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'semester_count' => __('Count of semesters'),
            'education_period' => __('Education Period (Year)'),
            '_department' => __('Faculty'),
            '_specialty_id' => __('Specialty'),
        ]);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty_id']);
    }

    public function getQualification()
    {
        return $this->hasOne(EQualification::className(), ['id' => '_qualification']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system']);
    }

    public function getSemesters()
    {
        return $this->hasMany(Semester::className(), ['_curriculum' => 'id'])->addOrderBy(['position' => SORT_ASC]);
    }

    public function getSubjects()
    {
        return $this->hasMany(ECurriculumSubject::class, ['_curriculum' => 'id'])->where(['e_curriculum_subject.active' => ECurriculumSubject::STATUS_ENABLE]);
    }

    public function getLastSemester()
    {
        return $this->hasOne(Semester::className(), ['_curriculum' => 'id'])
            ->andFilterWhere(['last' => true]);
    }

    public function semesterStatus($id)
    {
        $count = 0;
        foreach ($this->semesters as $item) {
            $count += $item->accepted ? 1 : 0;
        }
        return $count == count($this->semesters);
    }

    public function getEGroups()
    {
        return $this->hasMany(EGroup::className(), ['_curriculum' => 'id']);
    }

    public static function getByEducationYear($educationYear)
    {
        return ArrayHelper::map(
            self::find()->where(['active' => self::STATUS_ENABLE])->andWhere(
                ['_education_year' => $educationYear]
            )->orderByTranslationField('name')->all(),
            'id',
            'name'
        );
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
                    'name',
                    'id',
                    'position',
                    '_department',
                    '_specialty_id',
                    '_education_type',
                    '_education_form',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_marking_system) {
            $query->andFilterWhere(['_marking_system' => $this->_marking_system]);
        }
        return $dataProvider;
    }

    public function getShortName()
    {
        $title = StringHelper::truncateWords($this->name, 4);

        if (strlen($title) > 35) {
            return StringHelper::truncate($title, 35);
        }
        return $title;
    }
}
