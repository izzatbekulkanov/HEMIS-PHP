<?php

namespace common\models\employee;

use common\components\Config;
use common\components\db\PgQuery;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectTask;
use common\models\system\_BaseModel;
use common\models\system\AdminRole;
use common\models\system\classifier\EmployeeType;
use common\models\system\classifier\EmploymentForm;
use common\models\system\classifier\EmploymentStaff;
use common\models\system\classifier\Position;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\TeacherStatus;
use common\models\structure\EDepartment;
use common\models\system\classifier\TrainingType;
use common\models\system\SystemLog;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use Imagine\Image\ManipulatorInterface;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "e_employee_meta".
 *
 * @property int $id
 * @property string|null $employee_id_number
 * @property int $_employee
 * @property int $_employee_type
 * @property int $_department
 * @property string $_uid
 * @property boolean $_sync
 * @property string $_position
 * @property string $_employment_form
 * @property string $_employment_staff
 * @property string $_employee_status
 * @property string $contract_number
 * @property string $decree_number
 * @property DateTime $contract_date
 * @property DateTime $decree_date
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EDepartment $department
 * @property EmploymentForm $employmentForm
 * @property EmploymentStaff $employmentStaff
 * @property EmploymentStaff $employeeType
 * @property TeacherPositionType $staffPosition
 * @property TeacherStatus $employeeStatus
 * @property EEmployee $employee
 */
class EEmployeeMeta extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    protected $_translatedAttributes = ['name'];
    public $_employees;
    public $gender;
    public $months;
    public $_faculty;
    public $academic_degree;
    public $academic_rank;
    public $ip;
    public $message;
    public $_education_year;
    public $_semester;
    public $_curriculum;
    public $_subject;
    public $subject_name;
    public $_training_type;
    public $_education_lang;
    public $topics_count;
    public $resources_count;
    public $tasks_count;

    public static function tableName()
    {
        return 'e_employee_meta';
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

    public static function getTeachers($department = "")
    {
        if ($department == "") {
            return ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->andWhere(['in', '_position', [
                    TeacherPositionType::TEACHER_POSITION_TYPE_INTERN,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
                ]])
                //->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all(), '_employee', 'employee.fullName');
        } else {
            return ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->andWhere(['_department' => $department])
                ->andWhere(['in', '_position', [
                    TeacherPositionType::TEACHER_POSITION_TYPE_INTERN,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
                ]])
                //->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all(), '_employee', 'employee.fullName');
        }
    }

    public static function getTeacherList($department = "")
    {
        if ($department == "") {
            return self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->andWhere(['in', '_position', [
                    TeacherPositionType::TEACHER_POSITION_TYPE_INTERN,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
                ]])
                ->all();
        } else {
            return self::find()
                ->where(['active' => self::STATUS_ENABLE])
                // ->andWhere(['_department' => $department])
                ->andWhere(['in', '_department', $department])
                ->andWhere(['in', '_position', [
                    TeacherPositionType::TEACHER_POSITION_TYPE_INTERN,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER,
                    TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR,
                    TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
                ]])
                ->all();
        }
    }

    public static function getLeaderName($department = "", $post = "")
    {
        return self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->andWhere(['_department' => $department])
            ->andWhere(['in', '_position', [
                $post,
            ]])
            ->one();

    }

    public static function getRectorName()
    {
        return self::find()
            ->where([
                'active' => self::STATUS_ENABLE,
                '_employee_status' => TeacherStatus::TEACHER_STATUS_WORKING,
                '_position' => TeacherPositionType::TEACHER_POSITION_TYPE_RECTOR,
            ])
            ->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_employee', '_department', '_position', '_employment_form', '_employment_staff', '_employee_status', 'contract_number', 'contract_date'], 'required', 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],
            [['contract_date', 'contract_number', 'decree_date', 'decree_number'], 'safe'],
            [['_sync_status', '_faculty', '_education_year', '_semester'], 'safe'],
            [['contract_number'], 'string', 'max' => 64],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_employment_form'], 'exist', 'skipOnError' => true, 'targetClass' => EmploymentForm::className(), 'targetAttribute' => ['_employment_form' => 'code']],
            [['_employment_staff'], 'exist', 'skipOnError' => true, 'targetClass' => EmploymentStaff::className(), 'targetAttribute' => ['_employment_staff' => 'code']],
            [['_position'], 'exist', 'skipOnError' => true, 'targetClass' => TeacherPositionType::className(), 'targetAttribute' => ['_position' => 'code']],
            [['_employee_status'], 'exist', 'skipOnError' => true, 'targetClass' => TeacherStatus::className(), 'targetAttribute' => ['_employee_status' => 'code']],
            [['_employee_type'], 'exist', 'skipOnError' => true, 'targetClass' => EmployeeType::className(), 'targetAttribute' => ['_employee_status' => 'code']],
        ]);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEmploymentForm()
    {
        return $this->hasOne(EmploymentForm::className(), ['code' => '_employment_form']);
    }

    public function getEmploymentStaff()
    {
        return $this->hasOne(EmploymentStaff::className(), ['code' => '_employment_staff']);
    }

    public function getStaffPosition()
    {
        return $this->hasOne(TeacherPositionType::className(), ['code' => '_position']);
    }

    public function getEmployeeStatus()
    {
        return $this->hasOne(TeacherStatus::className(), ['code' => '_employee_status']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getEmployeeType()
    {
        return $this->hasOne(EmployeeType::className(), ['code' => '_employee_type']);
    }

    public function getEmployeeCompetition()
    {
        return $this->hasOne(EEmployeeCompetition::class, ['_employee' => '_employee'])->orderBy(['election_date' => SORT_DESC]);
    }

    public function getEmployeeDevelopment()
    {
        return $this->hasOne(EEmployeeProfessionalDevelopment::class, ['_employee' => '_employee'])->orderBy(['end_date' => SORT_DESC]);
    }

    public function getSubjectTopics($semester = false, $curriculum = false, $subject = false, $training_type = false)
    {
        return ECurriculumSubjectTopic::find()
            ->where(
                [
                    '_semester' => $semester,
                    '_curriculum' => $curriculum,
                    '_subject' => $subject,
                    '_training_type' => $training_type,
                ]);
    }

    public function getSubjectResources($education_year = false, $semester = false, $curriculum = false, $subject = false, $training_type = false, $education_lang = false)
    {
        return $this->hasMany(ESubjectResource::className(), ['_employee' => '_employee'])
            ->select('COUNT(_subject_topic)')
            ->andOnCondition([
                'resource_type' => ESubjectResource::RESOURCE_TYPE_RESOURCE,
                '_education_year' => $education_year,
                '_semester' => $semester,
                '_curriculum' => $curriculum,
                '_subject' => $subject,
                '_training_type' => $training_type,
                '_language' => $education_lang,
            ])
            ->groupBy(['_subject_topic']);
    }

    public function getSubjectTasks($education_year = false, $semester = false, $curriculum = false, $subject = false, $training_type = false, $education_lang = false)
    {
        return $this->hasMany(ESubjectTask::className(), ['_employee' => '_employee'])
            /* ->filterWhere([
                 '_education_year' => $education_year,
                 '_semester' => $semester,
                 '_curriculum' => $curriculum,
                 '_subject' => $subject,
                 '_training_type' => $training_type,
             ])*/
            ->andOnCondition([
                '_education_year' => $education_year,
                '_semester' => $semester,
                '_curriculum' => $curriculum,
                '_subject' => $subject,
                '_training_type' => $training_type,
                '_language' => $education_lang,
            ]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()
            ->joinWith(['employee']);
        $query->with(['staffPosition', 'department', 'employeeStatus', 'employmentForm', 'employmentStaff']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['_department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_position) {
            $query->andFilterWhere(['_position' => $this->_position]);
        }
        if ($this->_employment_form) {
            $query->andFilterWhere(['_employment_form' => $this->_employment_form]);
        }
        if ($this->_employment_staff) {
            $query->andFilterWhere(['_employment_staff' => $this->_employment_staff]);
        }
        if ($this->_employee_status) {
            $query->andFilterWhere(['_employee_status' => $this->_employee_status]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }


        if ($this->_sync_status) {
            $query->andFilterWhere(['e_employee_meta._sync_status' => $this->_sync_status]);
        }

        return $dataProvider;
    }

    public function search_status($params)
    {
        $this->load($params);

        $query = self::find()
            ->joinWith(['employee']);
        $query->with(['staffPosition', 'department', 'employeeStatus', 'employmentForm', 'employmentStaff']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['e_employee.second_name' => SORT_ASC],
                'attributes' => [
                    'e_employee.second_name'
                ],
            ],

            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['_department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_position) {
            $query->andFilterWhere(['_position' => $this->_position]);
        }
        if ($this->_employment_form) {
            $query->andFilterWhere(['_employment_form' => $this->_employment_form]);
        }
        if ($this->_employment_staff) {
            $query->andFilterWhere(['_employment_staff' => $this->_employment_staff]);
        }
        if ($this->_employee_status) {
            $query->andFilterWhere(['_employee_status' => $this->_employee_status]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }


        if ($this->_sync_status) {
            $query->andFilterWhere(['e_employee_meta._sync_status' => $this->_sync_status]);
        }

        return $dataProvider;
    }

    public function searchForEmployee(EEmployee $employee)
    {
        $query = self::find()
            ->andFilterWhere(['_employee' => $employee->id])
            ->joinWith(['employee']);
        $query->with(['staffPosition', 'department', 'employeeStatus', 'employmentForm', 'employmentStaff']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);


        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        $position = $this->staffPosition->getTranslation('name', Config::LANGUAGE_UZBEK);
        return $position . " ({$this->employee->getFullName()})";
    }

    public function beforeSave($insert)
    {
        if ($this->decree_date == null) {
            $this->decree_date = null;
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function searchForLog($params)
    {
        $this->load($params);
        /*  $roles = AdminRole::find()
              ->select(['id'])
              ->where(['status' => AdminRole::STATUS_ENABLE])
              ->andWhere(['in', 'code', [AdminRole::CODE_TEACHER, AdminRole::CODE_DEPARTMENT]])
              ->column();*/
        $subQuery = SystemLog::find()
            // ->leftJoin('e_admin_roles', 'e_admin_roles._admin=e_system_log._admin')
            ->select(['e_system_log._admin', 'MAX(created_at) AS created_at'])
            //  ->andWhere(['e_admin_roles._role'=>$roles])
            ->groupBy('e_system_log._admin');


        $query = self::find();
        //$query->joinWith(['employee']);
        $query->leftJoin('e_employee', 'e_employee.id=e_employee_meta._employee');
        $query->leftJoin('e_system_log', 'e_system_log._admin=e_employee._admin');
        $query->innerJoin(['l' => $subQuery], 'e_system_log._admin = l._admin AND e_system_log.created_at = l.created_at AND l._admin=e_employee._admin');
        $query->select([
            'e_employee_meta._employee',
            'e_employee.first_name',
            'e_employee.second_name',
            'e_employee.third_name',
            'e_employee_meta._department',

            'l.created_at',
            'e_system_log.ip',
            'e_system_log.message',
        ]);
        $query->distinct();
        //->joinWith(['employee']);
        //$query->leftJoin('e_employee', 'e_employee.id=e_employee_meta._employee');
        //  $query->leftJoin('e_admin', 'e_admin.id=e_employee._admin');
        //$query->rightJoin('e_system_log', 'e_system_log._admin=e_employee._admin');

        $query->with(['staffPosition', 'department']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [


                ],
            ],

            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['_department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_position) {
            $query->andFilterWhere(['_position' => $this->_position]);
        }
        if ($this->_employment_form) {
            $query->andFilterWhere(['_employment_form' => $this->_employment_form]);
        }
        if ($this->_employment_staff) {
            $query->andFilterWhere(['_employment_staff' => $this->_employment_staff]);
        }
        if ($this->_employee_status) {
            $query->andFilterWhere(['_employee_status' => $this->_employee_status]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }


        if ($this->_sync_status) {
            $query->andFilterWhere(['e_employee_meta._sync_status' => $this->_sync_status]);
        }
        $query->andFilterWhere(['in', '_position', TeacherPositionType::TEACHER_POSITIONS]);
        $query->andFilterWhere(['_employee_status' => TeacherStatus::TEACHER_STATUS_WORKING]);

        return $dataProvider;
    }

    public function searchForResources($params)
    {
        $this->load($params);

        $query = self::find();

        //$query->joinWith(['employee']);
        $query->leftJoin('e_employee', 'e_employee.id=e_employee_meta._employee');
        $query->leftJoin('e_subject_schedule', 'e_subject_schedule._employee=e_employee_meta._employee');
        $query->leftJoin('e_group', 'e_group.id=e_subject_schedule._group');
        //$query->leftJoin('e_curriculum_subject_detail', 'e_curriculum_subject_detail._subject=e_curriculum_subject._subject AND e_curriculum_subject_detail._curriculum=e_curriculum_subject._curriculum AND e_curriculum_subject_detail._semester=e_curriculum_subject._semester AND academic_load>0');
        //$query->leftJoin('e_curriculum_subject_topic', 'e_curriculum_subject_topic._subject=e_subject_schedule._subject AND e_curriculum_subject_topic._curriculum=e_subject_schedule._curriculum AND e_curriculum_subject_topic._semester=e_subject_schedule._semester AND e_curriculum_subject_topic._training_type=e_subject_schedule._training_type');

        $query->select([
            'e_subject_schedule._employee',

            'e_employee_meta._department',
            'e_subject_schedule._curriculum',
            'e_subject_schedule._subject',
            'e_subject_schedule._education_year',
            'e_subject_schedule._semester',
            'e_subject_schedule._training_type',
            'e_group._education_lang',
            //'COUNT(e_curriculum_subject_topic.id) AS topics_count',
        ]);
        $query->groupBy([
            'e_subject_schedule._employee',
            'e_employee_meta._department',
            'e_subject_schedule._curriculum',
            'e_subject_schedule._subject',
            'e_subject_schedule._education_year',
            'e_subject_schedule._semester',
            'e_subject_schedule._training_type',
            'e_group._education_lang',
            // 'e_subject_task._training_type',
        ]);
        //$query->distinct();
        //->joinWith(['employee']);
        //$query->leftJoin('e_employee', 'e_employee.id=e_employee_meta._employee');
        //  $query->leftJoin('e_admin', 'e_admin.id=e_employee._admin');
        //$query->rightJoin('e_system_log', 'e_system_log._admin=e_employee._admin');

        $query->with(['staffPosition', 'department']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['e_subject_schedule._employee' => SORT_ASC, 'e_subject_schedule._subject' => SORT_ASC, 'e_subject_schedule._training_type' => SORT_ASC],
                'attributes' => [
                    'e_subject_schedule._employee',
                    'e_employee_meta._department',
                    'e_subject_schedule._curriculum',
                    'e_employee_meta._employee',
                    'e_subject_schedule._subject',
                    'e_subject_schedule._semester',
                    'e_subject_schedule._training_type',
                    'e_group._education_lang',

                ],
            ],

            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }
        $query->andFilterWhere([
            '<>', 'e_subject_schedule._training_type', TrainingType::TRAINING_TYPE_INDEPENDENT,
        ]);
        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['e_employee_meta._department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_employee_meta._department' => $this->_department]);
        }
        if ($this->_position) {
            $query->andFilterWhere(['_position' => $this->_position]);
        }
        if ($this->_employment_form) {
            $query->andFilterWhere(['_employment_form' => $this->_employment_form]);
        }
        if ($this->_employment_staff) {
            $query->andFilterWhere(['_employment_staff' => $this->_employment_staff]);
        }
        if ($this->_employee_status) {
            $query->andFilterWhere(['_employee_status' => $this->_employee_status]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_schedule._education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['e_subject_schedule._semester' => $this->_semester]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }


        if ($this->_sync_status) {
            $query->andFilterWhere(['e_employee_meta._sync_status' => $this->_sync_status]);
        }
        $query->andFilterWhere(['in', '_position', TeacherPositionType::TEACHER_POSITIONS]);
        $query->andFilterWhere(['_employee_status' => TeacherStatus::TEACHER_STATUS_WORKING]);

        return $dataProvider;
    }

    public static function shortTitle($message = false)
    {
        $title = StringHelper::truncateWords($message, 12);

        if (strlen($title) > 120) {
            return StringHelper::truncate($title, 120);
        }
        return $title;
    }

    public function searchForTutor($params, $faculty = null)
    {
        $this->load($params);

        $query = self::find()
            ->joinWith(['employee']);
        $query->with(['staffPosition', 'department', 'employeeStatus', 'employmentForm', 'employmentStaff']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['e_employee.second_name' => SORT_ASC],
                'attributes' => [
                    'e_employee.second_name'
                ],
            ],

            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['_department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_position) {
            $query->andFilterWhere(['_position' => $this->_position]);
        }
        if ($this->_employment_form) {
            $query->andFilterWhere(['_employment_form' => $this->_employment_form]);
        }
        if ($this->_employment_staff) {
            $query->andFilterWhere(['_employment_staff' => $this->_employment_staff]);
        }
        if ($this->_employee_status) {
            $query->andFilterWhere(['_employee_status' => $this->_employee_status]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }


        if ($this->_sync_status) {
            $query->andFilterWhere(['e_employee_meta._sync_status' => $this->_sync_status]);
        }

        return $dataProvider;
    }

    public static function generateDownloadFile(QueryInterface $query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Employees'));
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $row = 1;
        $col = 1;
        $cols = range('A', 'Z');

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employee ID'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Citizenship'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Faculty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Department'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('First Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Second Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Third Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employment Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Position'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Academic Degree'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Academic Rank'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Ishga kirgan yili'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Contract Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Decree Number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Decree Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Birth Date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Yoshi'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport Number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport Pin'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Gender'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Email'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Telephone'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Home Address'), DataType::TYPE_STRING);

        $sheet->getStyle('A1:' . $cols[$col - 1] . '1')
            ->applyFromArray($headerStyle)
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(false);

        foreach ($cols as $colName)
            $sheet->getColumnDimension($colName)->setAutoSize(true);

        function sheet(Worksheet $sheet, $col, $row, $value, $type = DataType::TYPE_STRING)
        {
            $sheet->setCellValueExplicitByColumnAndRow($col, $row, $value, $type);
        }

        /**
         * @var $model EEmployeeMeta
         */
        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;
            $e = $model->employee;

            sheet($sheet, $col++, $row, $e->employee_id_number);
            sheet($sheet, $col++, $row, $e->citizenshipType ? $e->citizenshipType->name : '');
            sheet($sheet, $col++, $row, $model->department->parentDepartment ? $model->department->parentDepartment->name : '');
            sheet($sheet, $col++, $row, $model->department->name);
            sheet($sheet, $col++, $row, $e->first_name);
            sheet($sheet, $col++, $row, $e->second_name);
            sheet($sheet, $col++, $row, $e->third_name);
            sheet($sheet, $col++, $row, $model->employmentForm->name);
            sheet($sheet, $col++, $row, $model->staffPosition->name);
            sheet($sheet, $col++, $row, $e->_academic_rank != '10' ? $e->academicDegree->name : '');
            sheet($sheet, $col++, $row, $e->_academic_degree != '10' ? $e->academicRank->name : '');
            sheet($sheet, $col++, $row, $e->specialty);
            sheet($sheet, $col++, $row, $e->year_of_enter);
            sheet($sheet, $col++, $row, $model->contract_number);
            sheet($sheet, $col++, $row, $model->contract_date ? formatDate($model->contract_date) : '');
            sheet($sheet, $col++, $row, $model->decree_number);
            sheet($sheet, $col++, $row, $model->decree_date ? formatDate($model->decree_date) : '');
            sheet($sheet, $col++, $row, $e->birth_date ? formatDate($e->birth_date) : '');
            sheet($sheet, $col++, $row, $e->birth_date ? date('Y') - $e->birth_date->format('Y') : '');
            sheet($sheet, $col++, $row, $e->passport_number);
            sheet($sheet, $col++, $row, $e->passport_pin);
            sheet($sheet, $col++, $row, $e->gender->name);
            sheet($sheet, $col++, $row, $e->email);
            sheet($sheet, $col++, $row, $e->telephone);
            sheet($sheet, $col++, $row, $e->home_address);
        }

        $name = 'Lavozimlar-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
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
