<?php

namespace common\models\student;

use common\models\curriculum\MarkingSystem;
use common\models\curriculum\Semester;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\classifier\Language;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationForm;
use common\models\structure\EDepartment;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\StudentStatus;
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
 * This is the model class for table "e_group".
 *
 * @property int $id
 * @property string $name
 * @property int $_department
 * @property string $_education_type
 * @property string $_education_form
 * @property string $_specialty
 * @property int $_curriculum
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EDepartment $department
 * @property ESpecialty $specialty
 * @property EducationForm $educationForm
 * @property EducationType $educationType
 * @property Language $educationLang
 */
class EGroup extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'create';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_group';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getOptions($curriculum = "")
    {
        $result = ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE, '_curriculum' => $curriculum])
            //->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
        /*
        if($faculty=="") {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE, '_curriculum' => $curriculum])
                //->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }
        else{
            $result = ArrayHelper::map(self::find()
                ->joinWith(['specialty'])
                ->where(['e_group.active' => self::STATUS_ENABLE, 'e_specialty._department'=>$faculty])
                //->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
                //->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }*/
        return $result;
    }

    public static function getOptionsByFaculty($faculty = "", $specialty = "")
    {
        return ArrayHelper::map(self::find()
            ->joinWith(['specialty'])
            ->where(['e_group.active' => self::STATUS_ENABLE, 'e_specialty._department' => $faculty, 'e_specialty.id' => $specialty])
            //   ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getOptionsBySpecialty($specialty)
    {
        return ArrayHelper::map(self::find()
            ->joinWith(['specialty'])
            ->where(['e_group.active' => self::STATUS_ENABLE, 'e_specialty.id' => $specialty])
            //   ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getOptionsByFacultyEduForm($faculty = "", $specialty = "", $education_form = "")
    {
        return ArrayHelper::map(self::find()
            ->joinWith(['specialty'])
            ->where(['e_group.active' => self::STATUS_ENABLE, 'e_specialty._department' => $faculty, '_specialty_id' => $specialty, '_education_form' => $education_form])
            //   ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getOptionsByFacultyEduFormEduType($faculty = "", $education_type = "", $education_form = "")
    {
        return ArrayHelper::map(self::find()
            ->where(['e_group.active' => self::STATUS_ENABLE, '_department' => $faculty, '_education_type' => $education_type, '_education_form' => $education_form])
            //   ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', '_curriculum', '_education_lang'], 'required', 'on' => self::SCENARIO_INSERT],
            //[['name', '_department', '_education_type', '_education_form', '_specialty', '_curriculum'], 'required', 'on' => self::SCENARIO_INSERT],
            [['_department', '_curriculum', 'position'], 'default', 'value' => null],
            [['_department', '_curriculum', 'position', '_specialty_id'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['name'], 'string', 'max' => 256],
            [['_education_type', '_education_form', '_education_lang'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_specialty_id'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty_id' => 'id']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_education_lang'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['_education_lang' => 'code']],
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty_id']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationLang()
    {
        return $this->hasOne(Language::className(), ['code' => '_education_lang']);
    }


    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system'])
            ->viaTable('e_curriculum', ['id' => '_curriculum']);
    }

    protected $_semesterObject = false;

    public function getSemesterObject()
    {
        if ($this->_semesterObject === false) {
            /**
             * @var $meta EStudentMeta
             */
            $meta = EStudentMeta::find()->where([
                '_group' => $this->id,
                'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])
                ->limit(1)
                ->one();
            if ($meta) {
                $this->_semesterObject = $meta->semester;
            } else {
                $this->_semesterObject = null;
            }
        }

        return $this->_semesterObject;
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_department' => __('Faculty'),
            '_curriculum' => __('Curriculum Curriculum'),
            '_specialty_id' => __('Specialty'),
        ]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['specialty']);
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
                    '_education_lang',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_group.name_uz', $this->search, 'e_group._translations');
            $query->orWhereLike('e_group.name_oz', $this->search, 'e_group._translations');
            $query->orWhereLike('e_group.name_ru', $this->search, 'e_group._translations');
            $query->orWhereLike('e_group.name', $this->search);
        }
        if ($this->_department) {
            $query->andFilterWhere(['e_group._department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_group._education_form' => $this->_education_form]);
        }
        if ($this->_education_lang) {
            $query->andFilterWhere(['_education_lang' => $this->_education_lang]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        return $dataProvider;
    }

    public static function getGroupStudentsOptions($groupId)
    {
        $model = new EStudentMeta();
        $model->_group = $groupId;
        $query = $model->searchContingent([], null, false);
        return ArrayHelper::map($query->all(), '_student', 'student.fullName');
    }


    public function searchForTutor($params, Admin $admin, $faculty = null)
    {
        $this->load($params);

        $query = self::find()->with(['specialty']);

        if ($this->search) {
            $query->orWhereLike('e_group.name_uz', $this->search, 'e_group._translations');
            $query->orWhereLike('e_group.name_oz', $this->search, 'e_group._translations');
            $query->orWhereLike('e_group.name_ru', $this->search, 'e_group._translations');
            $query->orWhereLike('e_group.name', $this->search);
        }

        if ($faculty) {
            $query->andFilterWhere(['e_group._department' => $faculty]);
        } else {
            $query->andFilterWhere(['e_group._department' => $admin->employee->getDepartments()->select(['id'])->column()]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_group._education_form' => $this->_education_form]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'name',
                    'id',
                    'position',
                    '_department',
                    '_specialty_id',
                    '_education_type',
                    '_education_form',
                    '_education_lang',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }
}
