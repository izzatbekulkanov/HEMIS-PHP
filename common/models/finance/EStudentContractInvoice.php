<?php

namespace common\models\finance;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_contract_invoice".
 *
 * @property int $id
 * @property int|null $_student_contract
 * @property string|null $_education_year
 * @property int $_student
 * @property int $_department
 * @property int $_specialty
 * @property string $_education_type
 * @property string $_education_form
 * @property int $_curriculum
 * @property int $_group
 * @property string|null $invoice_number
 * @property string $invoice_date
 * @property float $invoice_summa
 * @property string|null $invoice_status
 * @property string|null $filename
 * @property string|null $_translations
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EDepartment $department
 * @property EEducationYear $educationYear
 * @property EGroup $group
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EStudentContract $studentContract
 * @property HEducationForm $educationForm
 * @property HEducationType $educationType
 */
class EStudentContractInvoice extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    public static function tableName()
    {
        return 'e_student_contract_invoice';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getAllSummaByContract($contract = false)
    {
        return self::find()
            ->select('SUM (invoice_summa) as invoice_summa')
            ->where([
                '_student_contract' => $contract,
                'active' => self::STATUS_ENABLE,
            ])
            //->orderByTranslationField('position')
            ->one();
    }
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_student_contract', '_student', '_department', '_specialty', '_curriculum', '_group', 'position'], 'default', 'value' => null],
            [['_student_contract', '_student', '_department', '_specialty', '_curriculum', '_group', 'position'], 'integer'],
            //[['_student', '_department', '_specialty', '_education_type', '_education_form', '_curriculum', '_group', 'invoice_date', 'invoice_summa'], 'required', 'on'=> self::SCENARIO_CREATE],
            [['invoice_number', 'invoice_date', 'invoice_summa'], 'required', 'on'=> self::SCENARIO_CREATE],
            [['invoice_date', 'filename', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['invoice_summa'], 'number'],
            [['active'], 'boolean'],
            [['_education_year', '_education_type', '_education_form', 'invoice_status', '_level'], 'string', 'max' => 64],
            [['invoice_number'], 'string', 'max' => 255],
            [['invoice_number'], 'unique'],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_student_contract'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentContract::className(), 'targetAttribute' => ['_student_contract' => 'id']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_level'], 'exist', 'skipOnError' => true, 'targetClass' => Course::className(), 'targetAttribute' => ['_level' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_department' => __('Structure Faculty'),
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

    public function getStudentContract()
    {
        return $this->hasOne(EStudentContract::className(), ['id' => '_student_contract']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += @$item[@$fieldName];
        }

        return @$total;
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
                    'invoice_number',
                    'invoice_date',
                    'invoice_summa',
                    '_student_contract',
                    '_education_year',
                    '_student',
                    '_department',
                    '_specialty',
                    '_education_type',
                    '_education_form',
                    '_level',
                    '_curriculum',
                    '_group',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_student_contract) {
            $query->andFilterWhere(['_student_contract' => $this->_student_contract]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
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
        if ($this->_level) {
            $query->andFilterWhere(['_level' => $this->_level]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        return $dataProvider;
    }

    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        $query->joinWith(
            [
                //   'curriculum',
                'student'
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
            $query->andFilterWhere(['e_student_contract_invoice._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_contract_invoice._education_type' => $this->_education_type]);
        }

        if ($this->_specialty) {
            $query->andFilterWhere(['e_student_contract_invoice._specialty' => $this->_specialty]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_contract_invoice._education_form' => $this->_education_form]);
        }

        if ($this->_level) {
            $query->andFilterWhere(['e_student_contract_invoice._level' => $this->_level]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_student_contract_invoice._group' => $this->_group]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_contract_invoice._curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'invoice_date' => SORT_DESC,
                    ],
                    'attributes' => [
                        'e_student.second_name',
                        'e_student.first_name',
                        'e_student.third_name',
                        'invoice_number',
                        'invoice_date',
                        'invoice_summa',
                        '_student_contract',
                        '_education_year',
                        '_student',
                        '_department',
                        '_specialty',
                        '_education_type',
                        '_education_form',
                        '_curriculum',
                        '_group',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
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

    public function getLevelItems()
    {
        return ArrayHelper::map(
            Course::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_level'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getCurriculumItems()
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'id' => self::find()
                        ->select(['_curriculum'])
                        ->distinct()
                        ->column()])
                ->all(), 'id', 'name');
    }


    public function getGroupItems()
    {
        $query = EGroup::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_group'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
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
}
