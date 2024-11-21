<?php

namespace common\models\archive;

use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationType;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use Yii;

/**
 * This is the model class for table "e_graduate_qualifying_work".
 *
 * @property int $id
 * @property string $name
 * @property string $_specialty
 * @property string $_education_type
 * @property string $_education_year
 * @property int $_student
 * @property int $_decree
 * @property int $_faculty
 * @property bool|null $active
 *
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EDecree $decree
 * @property ECertificateCommitteeResult $certificateCommitteeResult
 */
class EGraduateQualifyingWork extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    protected $_translatedAttributes = ['work_name'];

    public static function tableName()
    {
        return 'e_graduate_qualifying_work';
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'work_name' => __('Graduate work name') . ' º'
            ]
        );
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
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'work_name',
                        'supervisor_name',
                        'supervisor_work',
                        '_education_type',
                        '_education_year',
                        '_specialty',
                        '_faculty',
                        '_department',
                        '_group',
                        '_decree',
                        '_student'
                    ],
                    'required',
                    'on' => self::SCENARIO_INSERT
                ],
                [['_student', '_faculty', '_department', '_specialty', '_group'], 'integer'],
                [['active'], 'boolean'],
                [['work_name'], 'string', 'max' => 500],
                [
                    ['supervisor_name', 'supervisor_work', 'advisor_name', 'advisor_work'],
                    'string',
                    'max' => 255
                ],
                [['_student'], 'unique'],
                [
                    ['_specialty'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESpecialty::className(),
                    'targetAttribute' => ['_specialty' => 'id']
                ],
                [
                    ['_faculty'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EDepartment::className(),
                    'targetAttribute' => ['_faculty' => 'id']
                ],
                [
                    ['_department'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EDepartment::className(),
                    'targetAttribute' => ['_department' => 'id']
                ],
                [
                    ['_student'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EStudent::className(),
                    'targetAttribute' => ['_student' => 'id']
                ],
                [
                    ['_education_type'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationType::className(),
                    'targetAttribute' => ['_education_type' => 'code']
                ],
                [
                    ['_education_year'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationYear::className(),
                    'targetAttribute' => ['_education_year' => 'code']
                ],
            ]
        );
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getFaculty()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_faculty']);
    }

    public function getDecree()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getCertificateCommitteeResult()
    {
        return $this->hasOne(ECertificateCommitteeResult::className(), ['_graduate_work' => 'id']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function beforeSave($insert)
    {
        if ($this->isAttributeChanged('_decree', false)) {
            if ($did = $this->getOldAttribute('_decree')) {
                EDecreeStudent::deleteAll(['_decree' => $did, '_student' => $this->_student]);
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->decree->registerStudent($this->student, \Yii::$app->user->identity);

        parent::afterSave($insert, $changedAttributes);
    }

    public function search($params, $asProvider = true)
    {
        $this->load($params);
        $query = self::find();
        $defaultOrder = ['created_at' => SORT_DESC];

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_faculty) {
            $query->andFilterWhere(['_faculty' => $this->_faculty]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
                            'updated_at',
                            'created_at',
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

    public static function getSelectOptions($student = "", $eduYear = "")
    {
        if ($student != "") {
            $query = self::find()->select(['id', 'work_name', 'active', '_translations', '_education_year'])->where(['active' => true, '_student' => $student]);
            if ($eduYear != "") {
                $query->andWhere(['_education_year' => $eduYear]);
            }
            return ArrayHelper::map(
                $query->all(),
                'id',
                'work_name'
            );
        }
        return ArrayHelper::map(
            self::find()->select(['id', 'work_name', 'active'])->where(['active' => true])->all(),
            'id',
            'work_name'
        );
    }

    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Archive Graduate Work'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Archive Graduate Work'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Supervisor Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Supervisor Work'), DataType::TYPE_STRING);

        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->group ? $model->group->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->student->getFullName(),
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
                $model->work_name,
                DataType::TYPE_STRING
            );
            $supervisor = "";
            if (empty($model->advisor_name)) {
                $supervisor = $model->supervisor_name;
            }
            $supervisor = $model->supervisor_name . ' - ' . $model->advisor_name;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $supervisor,
                DataType::TYPE_STRING
            );
            $supervisor_work_name = "";
            if (empty($model->advisor_work)) {
                $supervisor_work_name = $model->supervisor_work;
            }
            $supervisor_work_name = $model->supervisor_work . ' - ' . $model->advisor_work;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $supervisor_work_name,
                DataType::TYPE_STRING
            );
        }

        $name = 'BMI va MD mavzulari-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

}
