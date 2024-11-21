<?php

namespace common\models\archive;

use common\components\Config;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\system\Admin;
use Yii;
use common\models\system\_BaseModel;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_reference".
 *
 * @property int $id
 * @property int $_student_meta
 * @property int $_student
 * @property int $_department
 * @property int $_specialty
 * @property int $_group
 * @property string $_education_type
 * @property string $_education_form
 * @property string $_education_year
 * @property int|null $_curriculum
 * @property string $_semester
 * @property string $_level
 * @property string|null $university_name
 * @property string|null $first_name
 * @property string|null $second_name
 * @property string|null $third_name
 * @property string|null $passport_pin
 * @property string|null $birth_date
 * @property int $year_of_enter
 * @property string $_citizenship
 * @property string $_payment_form
 * @property string|null $reference_number
 * @property string $reference_date
 * @property string|null $hash
 * @property string|null $filename
 * @property string|null $_translations
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EDepartment $department
 * @property EducationYear $educationYear
 * @property ESpecialty $specialty
 * @property EGroup $group
 * @property EStudent $student
 * @property EStudentMeta $studentMeta
 * @property CitizenshipType $citizenship
 * @property Course $level
 * @property EducationForm $educationForm
 * @property EducationType $educationType
 * @property PaymentForm $paymentForm
 */
class EStudentReference extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    //const SCENARIO_INSERT = 'insert';

    public static function tableName()
    {
        return 'e_student_reference';
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
            //[['_student_meta', '_student', '_department', '_specialty', '_education_type', '_education_form', '_education_year', '_semester', '_level', 'year_of_enter', '_citizenship', '_payment_form', 'reference_date'], 'required'],
            [['_student_meta', '_student', '_department', '_specialty', '_curriculum', 'year_of_enter', 'position'], 'default', 'value' => null],
            [['_student_meta', '_student', '_department', '_specialty', '_curriculum', 'year_of_enter', 'position', '_group'], 'integer'],
            [['birth_date', 'reference_date', 'filename', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_type', '_education_form', '_education_year', '_semester', '_level', '_citizenship', '_payment_form'], 'string', 'max' => 64],
            [[
                'university_name',
                'reference_number',
                'department_name',
                'specialty_name',
                'group_name',
                'education_type_name',
                'education_form_name',
                'education_year_name',
                'curriculum_name',
                'semester_name',
                'level_name',
                'citizenship_name',
                'payment_form_name',
            ], 'string', 'max' => 255],
            [['first_name', 'second_name', 'third_name'], 'string', 'max' => 100],
            [['passport_pin'], 'string', 'max' => 20],
            [['hash'], 'string', 'max' => 36],
            [['_student', '_specialty', '_education_year', '_semester', '_education_form'], 'unique', 'targetAttribute' => ['_student', '_specialty', '_education_year', '_semester', '_education_form']],
            [['hash'], 'unique'],
            [['reference_number'], 'unique'],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_student_meta'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentMeta::className(), 'targetAttribute' => ['_student_meta' => 'id']],
            [['_citizenship'], 'exist', 'skipOnError' => true, 'targetClass' => CitizenshipType::className(), 'targetAttribute' => ['_citizenship' => 'code']],
            [['_level'], 'exist', 'skipOnError' => true, 'targetClass' => Course::className(), 'targetAttribute' => ['_level' => 'code']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_payment_form'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentForm::className(), 'targetAttribute' => ['_payment_form' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
                '_curriculum' => __('Curriculum Curriculum'),
            ]
        );
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getGroup()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_group']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['id' => '_student_meta']);
    }

    public function getCitizenship()
    {
        return $this->hasOne(CitizenshipType::className(), ['code' => '_citizenship']);
    }

    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
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

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester', '_curriculum' => '_curriculum']);
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        // $query->joinWith(['studentMeta']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    '_student',
                    'reference_number',
                    'reference_date',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_education_year) {
            $query->andWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andWhere(['_student' => $this->_student]);
        }
        return $dataProvider;
    }

    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        if ($this->hasErrors()) {
            return false;
        }

        if ($this->hash == null) {
            $this->hash = gen_uuid();
        }

        return $result;
    }

    public function getFullName()
    {
        return strtoupper(trim($this->second_name . ' ' . $this->first_name . ' ' . $this->third_name));
    }

    public function getDepartmentItems()
    {
        return ArrayHelper::map(
            EDepartment::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => self::find()
                    ->select(['_department'])
                    ->distinct()
                    ->column()])
                ->all(), 'id', 'name');
    }


    public function getEducationTypeItems()
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_type'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getEducationFormItems()
    {
        return ArrayHelper::map(
            EducationForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_form'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getCurriculumItems($department)
    {
        $query = ECurriculum::find()
            ->orderByTranslationField('name');
        $query->where([
                'active' => true,
                'id' => self::find()
                    ->select(['_curriculum'])
                    ->distinct()
                    ->column()
            ]);
        if($department){
            $query->andWhere([
                '_department'=>$department
            ]);
        }
        return ArrayHelper::map(
            $query->all(), 'id', 'name');
    }
    public function getSpecialtyItems()
    {
        $query = ESpecialty::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_specialty'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function getGroupItems($department, $curriculum)
    {
        $query = EGroup::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_group'])
                ->distinct()
                ->column(),
                ]);
        if($department){
            $query->andWhere([
                '_department'=>$department
            ]);
        }
        if($curriculum){
            $query->andWhere([
                '_curriculum'=>$curriculum
            ]);
        }
        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        $query->joinWith(
            [
                //   'curriculum',
                //     'specialty',
                //       'group',
                'student'
                //           'department',
                //             'educationYear',
                //              'educationType',
//                'educationForm'
            ]
        );


        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        /*if ($department) {
            $this->_department = $department;
        }*/

        if ($this->_department) {
            $query->andFilterWhere(['e_student_reference._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_reference._education_type' => $this->_education_type]);
        }

        if ($this->_specialty) {
            $query->andFilterWhere(['e_student_reference._specialty' => $this->_specialty]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_reference._group' => $this->_group]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_reference._education_form' => $this->_education_form]);
        }


        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_reference._curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'reference_date' => SORT_DESC,
                    ],
                    'attributes' => [
                        '_department',
                        '_curriculum',
                        'e_student_reference.second_name',
                        'e_student_reference.first_name',
                        'e_student_reference.third_name',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_group',
                        '_specialty',
                        'created_at',
                        'reference_number',
                        'reference_date',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }



}
