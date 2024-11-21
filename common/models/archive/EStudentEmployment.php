<?php

namespace common\models\archive;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\StudentEmploymentUpdater;
use common\components\hemis\sync\StudentSportUpdater;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;

use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\Gender;
use common\models\system\classifier\GraduateFieldsType;
use common\models\system\classifier\GraduateInactiveType;
use common\models\system\classifier\PaymentForm;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "e_student_employment".
 *
 * @property int $id
 * @property int $_student
 * @property EStudent $studentData
 * @property string $student_id_number
 * @property string $employment_doc_number
 * @property string $employment_doc_date
 * @property string $company_name
 * @property string $position_name
 * @property string $_education_year
 * @property string $start_date
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EStudent $student0
 * @property EducationYear $educationYear
 */
class EStudentEmployment extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SCENARIO_INSERT_REASON = 'register_reason';

    const EMPLOYMENT_STATUS_MASTER = '11';
    const EMPLOYMENT_STATUS_EMPLOYEE = '12';
    const EMPLOYMENT_STATUS_REASON = '13';
    const EMPLOYMENT_STATUS_ORDINATOR = '14';
    const EMPLOYMENT_STATUS_DOCTORATE = '15';
    const EMPLOYMENT_STATUS_SECOND_HIHGER = '16';
    const EMPLOYMENT_STATUS_RETRAINING = '17';
    const EMPLOYMENT_WORKPLACE_COMPATIBILITY_SUITABLE = '11';
    const EMPLOYMENT_WORKPLACE_COMPATIBILITY_NOT_SUITABLE = '12';

    protected $_translatedAttributes = ['name'];
    public $_students;

    public static function tableName()
    {
        return 'e_student_employment';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getEmploymentStatusOptions()
    {
        return [
            self::EMPLOYMENT_STATUS_MASTER => __('Master status'), // Ўқишга кирган
            self::EMPLOYMENT_STATUS_EMPLOYEE => __('Employee status'), // Ишга жойлашган
            self::EMPLOYMENT_STATUS_ORDINATOR => __('Ordinator status'), // Ординатура
            self::EMPLOYMENT_STATUS_DOCTORATE => __('Doctorate status'), // Докторантура
            self::EMPLOYMENT_STATUS_SECOND_HIHGER => __('Second Higher status'), // Иккинчи олий
            self::EMPLOYMENT_STATUS_RETRAINING => __('Retraining status'), // Қайта тайёрлов
            self::EMPLOYMENT_STATUS_REASON => __('Employee with  reason'), // Сабабли жойлашмаган
        ];
    }

    public static function getWorkplaceCompatibilityStatusOptions()
    {
        return [
            self::EMPLOYMENT_WORKPLACE_COMPATIBILITY_SUITABLE => __('Entered work in accordance with the direction'), // Йўналишига мос ишга жойлаган
            self::EMPLOYMENT_WORKPLACE_COMPATIBILITY_NOT_SUITABLE => __('He got into a job that didn\'t fit his direction'), // Йўналишига мос бўлмаган ишга жойлашган
        ];
    }

    public function getEmploymentStatusLabel()
    {
        return self::getEmploymentStatusOptions()[$this->_employment_status];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //  [['employment_doc_number', 'employment_doc_date', 'company_name', 'position_name', 'start_date', '_employment_status', '_graduate_fields_type', 'workplace_compatibility'], 'required'],
            //[['employment_doc_number', 'employment_doc_date', 'company_name', 'position_name', 'start_date', '_employment_status', '_graduate_fields_type', 'workplace_compatibility'], 'required', 'on'=>self::SCENARIO_INSERT],
            //[['_employment_status', '_graduate_inactive'], 'required', 'on'=>self::SCENARIO_INSERT_REASON],
            [['_student', 'position'], 'default', 'value' => null],
            [['_student', 'position', '_department', '_specialty', '_group'], 'integer'],
            [['employment_doc_date', 'start_date', '_translations', 'updated_at', 'created_at', 'workplace_compatibility'], 'safe'],
            [['active'], 'boolean'],
            [['_employment_status', '_graduate_fields_type', '_graduate_inactive', 'workplace_compatibility', '_education_year', '_education_type', '_education_form', '_gender'], 'string', 'max' => 64],
            [['student', 'company_name', 'position_name'], 'string', 'max' => 256],
            [['student_id_number', 'employment_doc_number'], 'string', 'max' => 20],

            [['_employment_status', '_graduate_inactive'], 'required', 'when' => function ($model) {
                return $model->_employment_status == self::EMPLOYMENT_STATUS_REASON;
            }, 'whenClient' => "function (attribute, value) {return $('#_employment_status').val()==" . self::EMPLOYMENT_STATUS_REASON . ";}"],

            [[/*'employment_doc_number', 'employment_doc_date',*/ 'company_name', 'position_name', 'start_date', '_employment_status', '_graduate_fields_type', 'workplace_compatibility'], 'required', 'when' => function ($model) {
                return $model->_employment_status != self::EMPLOYMENT_STATUS_REASON;
            }, 'whenClient' => "function (attribute, value) {return $('#_employment_status').val()!=" . self::EMPLOYMENT_STATUS_REASON . ";}"],

            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_graduate_fields_type'], 'exist', 'skipOnError' => true, 'targetClass' => GraduateFieldsType::className(), 'targetAttribute' => ['_graduate_fields_type' => 'code']],
            [['_graduate_inactive'], 'exist', 'skipOnError' => true, 'targetClass' => GraduateInactiveType::className(), 'targetAttribute' => ['_graduate_inactive' => 'code']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_education_form'], 'exist', 'skipOnError' => true, 'targetClass' => EducationForm::className(), 'targetAttribute' => ['_education_form' => 'code']],
            [['_gender'], 'exist', 'skipOnError' => true, 'targetClass' => Gender::className(), 'targetAttribute' => ['_gender' => 'code']],
            [['_payment_form'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentForm::className(), 'targetAttribute' => ['_payment_form' => 'code']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_specialty'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_department' => __('Structure Faculty'),
            ]
        );
    }

    public function getStudentData()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getGraduateFieldsType()
    {
        return $this->hasOne(GraduateFieldsType::className(), ['code' => '_graduate_fields_type']);
    }

    public function getGraduateInactiveType()
    {
        return $this->hasOne(GraduateInactiveType::className(), ['code' => '_graduate_inactive']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getGender()
    {
        return $this->hasOne(Gender::className(), ['code' => '_gender']);
    }

    public function getPaymentForm()
    {
        return $this->hasOne(PaymentForm::className(), ['code' => '_payment_form']);
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
                    '_student',
                    'employment_doc_number',
                    'employment_doc_date',
                    '_graduate_fields_type',
                    '_graduate_inactive_type',
                    'company_name',
                    'position_name',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        /*if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }*/


        return $dataProvider;
    }

    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        if ($this->_education_year == null) {
            $this->_education_year = EducationYear::getCurrentYear()->code;
        }
        $years = $this->getEducationYearItems();

        if (!isset($years[$this->_education_year])) {
            $this->_education_year = null;
        }

        $query = self::find();
        $defaultOrder = ['employment_doc_date' => SORT_DESC];
        $query->joinWith(
            [
                //     'specialty',
                'studentData'
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
            $query->orWhereLike('e_student.uzasbo_id_number', $this->search);
        }

        /*if ($department) {
            $this->_department = $department;
        }*/

        if ($this->_employment_status) {
            $query->andFilterWhere(['e_student_employment._employment_status' => $this->_employment_status]);
        }

        if ($this->_graduate_fields_type) {
            $query->andFilterWhere(['e_student_employment._graduate_fields_type' => $this->_graduate_fields_type]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_student_employment._department' => intval($this->_department)]);
        }

        if ($this->_student) {
            $query->andFilterWhere(['e_student_employment._student' => intval($this->_student)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_employment._education_type' => $this->_education_type]);
        }

        if ($this->_specialty) {
            $query->andFilterWhere(['e_student_employment._specialty' => $this->_specialty]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_employment._education_form' => $this->_education_form]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_employment._education_year' => $this->_education_year]);
        }

        if ($this->_payment_form) {
            $query->andFilterWhere(['e_student_employment._payment_form' => $this->_payment_form]);
        }
        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
                            '_student',
                            /*'_student' => [
                                SORT_DESC => [
                                    'e_student.first_name' => SORT_DESC,
                                    'e_student.second_name' => SORT_DESC,
                                    'e_student.third_name' => SORT_DESC,
                                ],
                                SORT_ASC => [
                                    'e_student.first_name' => SORT_ASC,
                                    'e_student.second_name' => SORT_ASC,
                                    'e_student.third_name' => SORT_ASC,
                                ],
                            ],*/
                            '_department',
                            'e_student.second_name',
                            'e_student.first_name',
                            'e_student.third_name',

                            '_education_year',
                            '_education_type',
                            '_education_form',
                            '_specialty',
                            'created_at',
                            'employment_doc_number',
                            'employment_doc_date',
                            '_graduate_fields_type',
                            '_graduate_fields_type',
                            '_employment_status'
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]
            );
        } else {
            $query->addOrderBy($defaultOrder);
        }
        return $query;
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

    public function getEducationYearItems()
    {
        $items = ArrayHelper::map(
            EducationYear::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_year'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');

        return $items;
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

    public function getPaymentFormItems()
    {
        return ArrayHelper::map(
            PaymentForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_payment_form'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }

    public static function generateEmploymentDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Student Employment List'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('T/R'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Faculty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Payment Form'), DataType::TYPE_STRING);

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employment Doc Number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employment Doc Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Company Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Position Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Start Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employment Status'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Graduate Inactive'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Student Diploma'), DataType::TYPE_STRING);
        $sheet->getStyle("A$row:P$row")->getFont()->setBold(true);
        foreach ($query->all() as $i => $model) {
            //$employment = EStudentEmployment::findOne(['_student' => $model->_student]);
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $row - 1,
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                //$model->student->getFullName(),
                $model->student,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->group ? $model->group->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->department ? $model->department->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->specialty ? $model->specialty->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationForm ? $model->educationForm->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationType ? $model->educationType->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->paymentForm ? $model->paymentForm->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->employment_doc_number ? $model->employment_doc_number : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->employment_doc_date ? Yii::$app->formatter->asDate($model->employment_doc_date) : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->company_name ? $model->company_name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->position_name ? $model->position_name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->start_date ? Yii::$app->formatter->asDate($model->start_date) : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->_employment_status ? EStudentEmployment::getEmploymentStatusOptions()[$model->_employment_status] : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->graduateInactiveType ? $model->graduateInactiveType->name : '',
                DataType::TYPE_STRING
            );
            $diploma = EStudentDiploma::findOne(['_student' => $model->_student]);
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $diploma ? $diploma->diploma_number : '',
                DataType::TYPE_STRING
            );
        }
        foreach (range('A', 'P') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
            // $sheet->getStyle('K')->getAlignment()->setWrapText(true);
        }

        $sheet->calculateColumnWidths();

        $name = 'Student_employments-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public function getShortCompanyName()
    {
        $title = StringHelper::truncateWords($this->company_name, 5);

        if (strlen($title) > 45) {
            return StringHelper::truncate($title, 45);
        }
        return $title;
    }

    public function getShortGraduateFieldsTypeData()
    {
        $title = StringHelper::truncateWords(trim(($this->graduateFieldsType ? $this->graduateFieldsType->name : '-') . ' (' . ($this->workplace_compatibility ? EStudentEmployment::getWorkplaceCompatibilityStatusOptions()[$this->workplace_compatibility] : '-')) . ')', 5);
        if (strlen($title) > 50) {
            return StringHelper::truncate($title, 50);
        }
        return $title;
    }

    public function getDescriptionForSync()
    {
        return $this->studentData->getFullName() . ' / ' . $this->company_name;
    }

    public function checkToApi($updateIfDifferent = true)
    {
        $result = false;
        if (HEMIS_INTEGRATION) {
            $this->setAsShouldBeSynced();
            $result = StudentEmploymentUpdater::checkModel($this, $updateIfDifferent);

            if ($this->_sync_status != self::STATUS_ERROR)
                $this->setAsSyncPerformed();
        }
        return $result;
    }

    public function syncToApi($delete = false)
    {
        $result = false;

        if (HEMIS_INTEGRATION) {
            $this->setAsShouldBeSynced();
            $this->refresh();
            $result = StudentEmploymentUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
