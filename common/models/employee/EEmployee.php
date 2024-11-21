<?php

namespace common\models\employee;

use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\components\Translator;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Gender;
use common\models\structure\EDepartment;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\SystemLog;
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
use Imagine\Image\ManipulatorInterface;

/**
 * This is the model class for table "e_employee".
 *
 * @property int $id
 * @property string|null $employee_id_number
 * @property string $fullName
 * @property string $first_name
 * @property string $second_name
 * @property string $email
 * @property string $home_address
 * @property string $telephone
 * @property string|null $third_name
 * @property DateTime $birth_date
 * @property string $_gender
 * @property string $_citizenship
 * @property string $_uid
 * @property string $_sync
 * @property string $passport_number
 * @property string $passport_pin
 * @property string $_academic_degree
 * @property string $_academic_rank
 * @property integer $year_of_enter
 * @property string|null $specialty
 * @property string|null $image
 * @property int|null $position
 * @property int|null $_admin
 * @property bool|null $active
 * @property string|null $_translations
 *
 * @property AcademicDegree $academicDegree
 * @property AcademicRank $academicRank
 * @property CitizenshipType $citizenshipType
 * @property Gender $gender
 * @property Admin $admin
 * @property EDepartment[] $departments
 * @property EDepartment $deanFaculties
 * @property EDepartment $department
 * @property EDepartment $headDepartments
 * @property EEmployeeMeta[] $employeeMeta
 */
class EEmployee extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    protected $_translatedAttributes = ['first_name', 'second_name'];
    protected $_searchableAttributes = ['first_name', 'second_name', 'third_name', 'employee_id_number', 'passport_pin', 'passport_number'];

    public static function tableName()
    {
        return 'e_employee';
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

    public static function getEmployees()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            //->orderByTranslationField('name')
            ->all(), 'id', 'fullName');
    }

    public function rules()
    {
        $rules = array_merge(parent::rules(), [
            [['first_name', 'second_name', '_gender', '_academic_degree', '_academic_rank', 'home_address'], 'required', 'on' => self::SCENARIO_INSERT],
            [['image'], 'safe'],
            [['active'], 'boolean'],
            [['email'], 'email'],
            [['_sync_status'], 'safe'],
            [['home_address'], 'match', 'pattern' => '/^[A-Za-z\ 0-9\(\)\,\.\-\_\"\'‘’\/]+$/i', 'message' => __('Manzil ma\'lumotlari lotinda kiritilsin')],
            [['first_name', 'second_name', 'third_name'], 'match', 'pattern' => '/^[A-Za-z\ \-\'‘’`]+$/i', 'message' => __('Ism-familiya lotinda kiritilsin')],


            [['employee_id_number'], 'string', 'max' => 14],
            [['year_of_enter'], 'number', 'integerOnly' => true, 'min' => date('Y') - 20, 'max' => date('Y')],
            [['first_name', 'second_name', 'third_name'], 'string', 'max' => 64],
            [['telephone'], 'match', 'pattern' => '/^[\+\(]{0,2}[998]{0,3}[\)]{0,1}[ ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{3}[- ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{2}$/', 'message' => __('Wrong mobile phone number')],
            [['specialty'], 'string', 'max' => 255],

            // [['email'], 'unique'],
            [['_academic_degree'], 'exist', 'skipOnError' => true, 'targetClass' => AcademicDegree::className(), 'targetAttribute' => ['_academic_degree' => 'code']],
            [['_academic_rank'], 'exist', 'skipOnError' => true, 'targetClass' => AcademicRank::className(), 'targetAttribute' => ['_academic_rank' => 'code']],
            [['_gender'], 'exist', 'skipOnError' => true, 'targetClass' => Gender::className(), 'targetAttribute' => ['_gender' => 'code']],
            [['_citizenship'], 'exist', 'skipOnError' => true, 'targetClass' => CitizenshipType::className(), 'targetAttribute' => ['_citizenship' => 'code']],

        ]);

        return array_merge($rules, [
            [['passport_number', '_citizenship', 'birth_date'], 'required', 'on' => self::SCENARIO_INSERT],
            [['passport_number', 'passport_pin'], 'unique'],
            [['passport_number'], 'string', 'max' => 15],
            [['passport_pin'], 'string', 'max' => 20],
            [['passport_pin'], 'required', 'when' => function () {
                return $this->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_UZB || $this->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_NOTCITIZENSHIP;
            }, 'whenClient' => 'checkCitizenship', 'on' => self::SCENARIO_INSERT],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'year_of_enter' => __('Ishga kirgan yili'),
        ]);
    }

    public static function getYearOfEnterOptions()
    {
        $years = [];

        for ($i = date('Y'); $i > (date('Y') - 20); $i--)
            $years [$i] = $i;

        return $years;
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => '_admin']);
    }

    public function getSystemLog()
    {
        $roles = AdminRole::find()
            ->select(['id'])
            ->where(['status' => AdminRole::STATUS_ENABLE])
            ->andWhere(['in', 'code', [AdminRole::CODE_TEACHER, AdminRole::CODE_DEPARTMENT]])
            ->column();
        return SystemLog::find()
            ->leftJoin('e_admin_roles', 'e_admin_roles._admin=e_system_log._admin')
            ->where(['e_system_log._admin' => $this->_admin])
            ->andWhere(['e_admin_roles._role' => $roles])
            ->orderBy(['e_system_log.created_at' => SORT_DESC])
            ->one();
    }

    public function getAcademicDegree()
    {
        return $this->hasOne(AcademicDegree::className(), ['code' => '_academic_degree']);
    }

    public function getAcademicRank()
    {
        return $this->hasOne(AcademicRank::className(), ['code' => '_academic_rank']);
    }

    public function getGender()
    {
        return $this->hasOne(Gender::className(), ['code' => '_gender']);
    }


    public function getDepartments()
    {
        return $this->hasMany(EDepartment::className(), ['id' => '_department'])
            ->viaTable('e_employee_meta', ['_employee' => 'id'])->with(['structureType']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
            ->viaTable('e_employee_meta', ['_employee' => 'id'])->with(['structureType']);
    }

    public function getDeanFaculties()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
            ->viaTable('e_employee_meta', ['_employee' => 'id'], function ($query) {
                $ids = EDepartment::find()
                    ->select(['id'])
                    ->where(['active' => self::STATUS_ENABLE, '_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY])
                    ->column();

                $query->andFilterWhere(['e_employee_meta._department' => $ids, 'e_employee_meta._position' => [TeacherPositionType::TEACHER_POSITION_TYPE_DEAN, TeacherPositionType::TEACHER_POSITION_TYPE_TUTOR]]);

            })
            ->with(['structureType']);
    }

    public function getHeadDepartments()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
            ->viaTable('e_employee_meta', ['_employee' => 'id'], function ($query) {
                $ids = EDepartment::find()
                    ->select(['id'])
                    ->where(['active' => true, '_structure_type' => StructureType::STRUCTURE_TYPE_DEPARTMENT])
                    ->column();

                $query->andFilterWhere(['e_employee_meta._department' => $ids, 'e_employee_meta._position' => TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT]);

            })
            ->with(['structureType']);
    }

    public function getTeachers()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
            ->viaTable('e_employee_meta', ['_employee' => 'id'], function ($query) {
                $ids = EDepartment::find()
                    ->select(['id'])
                    ->where(['active' => self::STATUS_ENABLE, '_structure_type' => StructureType::STRUCTURE_TYPE_DEPARTMENT])
                    ->column();

                $query->andFilterWhere(['e_employee_meta._department' => $ids]);
                $query->andFilterWhere(['in', 'e_employee_meta._position', [TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT, TeacherPositionType::TEACHER_POSITION_TYPE_INTERN, TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER, TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR, TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR, TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT]]);

            })
            ->with(['structureType']);
    }

    public function getEmployeeMeta()
    {
        return $this->hasMany(EEmployeeMeta::className(), ['_employee' => 'id'])->with(['staffPosition']);
        // ->viaTable('e_employee_meta', ['_employee' => 'id'])//->with(['structureType']);
    }

    public function getFullName()
    {
        return trim($this->second_name . ' ' . $this->first_name . ' ' . $this->third_name);
    }

    public function getShortName()
    {
        return trim($this->second_name . ' ' . mb_substr($this->first_name, 0, 1) . '.' . mb_substr($this->third_name, 0, 1) . '.');
    }

    public function getCitizenshipType()
    {
        return $this->hasOne(CitizenshipType::className(), ['code' => '_citizenship']);
    }

    public function searchContingent($params, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        if ($this->search) {
            $query->orWhereLike('first_name', $this->search);
            $query->orWhereLike('second_name', $this->search);
            $query->orWhereLike('third_name', $this->search);
            $query->orWhereLike('passport_number', $this->search);
            $query->orWhereLike('passport_pin', $this->search);
            $query->orWhereLike('employee_id_number', $this->search);
        }

        if ($asProvider) {
            return new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['second_name' => SORT_ASC],
                    'attributes' => [
                        'second_name' => [
                            SORT_ASC => ['second_name' => SORT_ASC, 'first_name' => SORT_ASC, 'third_name' => SORT_ASC],
                            SORT_DESC => ['second_name' => SORT_DESC, 'first_name' => SORT_DESC, 'third_name' => SORT_DESC],
                        ],
                        'id',
                        'employee_id_number',
                        'position',
                        '_gender',
                        '_academic_degree',
                        'passport_number',
                        'passport_pin',
                        'birth_date',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        } else {
            $query->addOrderBy([
                'second_name' => SORT_ASC, 'first_name' => SORT_ASC, 'third_name' => SORT_ASC
            ]);
        }

        return $query;
    }


    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        if ($this->search) {
            $query->orWhereLike('first_name', $this->search);
            $query->orWhereLike('second_name', $this->search);
            $query->orWhereLike('third_name', $this->search);
            $query->orWhereLike('passport_number', $this->search);
            $query->orWhereLike('passport_pin', $this->search);
            $query->orWhereLike('employee_id_number', $this->search);
        }

        if ($this->_sync_status) {
            $query->andFilterWhere(['_sync_status' => $this->_sync_status]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
                'attributes' => [
                    'second_name' => [
                        SORT_ASC => ['second_name' => SORT_ASC, 'first_name' => SORT_ASC, 'third_name' => SORT_ASC],
                        SORT_DESC => ['second_name' => SORT_DESC, 'first_name' => SORT_DESC, 'third_name' => SORT_DESC],
                    ],
                    'id',
                    'employee_id_number',
                    'position',
                    '_gender',
                    '_academic_degree',
                    'passport_number',
                    'passport_pin',
                    'birth_date',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    public function createAdmin()
    {
        $model = new Admin();
        $model->full_name = trim(implode(" ", [$this->first_name, $this->second_name]));
        $model->login = Translator::getInstance()->translateToSlug($model->full_name, '_');
        $model->_employee = $this->id;
        $model->email = $this->email;
        $model->telephone = $this->telephone;
        $model->image = $this->image;

        return $model;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->admin) {
            $this->admin->updateAttributes([
                'full_name' => trim(implode(" ", [$this->first_name, $this->second_name])),
                'email' => $this->email,
                'telephone' => $this->telephone,
                'image' => $this->image,
            ]);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeSave($insert)
    {
        $this->passport_number = str_replace(' ', '', $this->passport_number);
        foreach (['first_name', 'second_name', 'third_name'] as $att) {
            $this->$att = mb_strtoupper($this->$att);
        }

        if (empty($this->passport_pin)) $this->passport_pin = null;

        return parent::beforeSave($insert);
    }

    public function getDescriptionForSync()
    {
        return $this->getFullName();
    }

    public function getRolesLabel()
    {
        $roles = [];
        if ($this->admin) {
            $roles = ArrayHelper::map($this->admin->roles, 'id', 'name');
        }
        return implode(', ', $roles);
    }


    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Employees'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employee ID'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Citizenship'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Gender'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Birth Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Academic Degree'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Academic Rank'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Ishga kirgan yili'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Email'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Telephone'), DataType::TYPE_STRING);

        /**
         * @var $model EEmployee
         */
        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->employee_id_number, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->getFullName(), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->citizenshipType ? $model->citizenshipType->name : '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->gender ? $model->gender->name : '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->birth_date instanceof DateTime ? $model->birth_date->format('Y-m-d') : $model->birth_date, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->academicDegree ? $model->academicDegree->name : '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->academicRank ? $model->academicRank->name : '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->specialty, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->year_of_enter, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->email, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->telephone, DataType::TYPE_STRING);
        }

        $name = 'Hodimlar-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public function getStaffPositionsLabel()
    {
        return implode(', ', ArrayHelper::getColumn($this->employeeMeta, 'staffPosition.name'));
    }
}
