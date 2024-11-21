<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\curriculum\EducationYear;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\ScientificPlatform;
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
 * This is the model class for table "e_scientific_platform_profile".
 *
 * @property int $id
 * @property int $_employee
 * @property string $_scientific_platform
 * @property string $profile_link
 * @property int|null $h_index
 * @property int|null $publication_work_count
 * @property int|null $citation_count
 * @property bool|null $is_checked
 * @property string|null $is_checked_date
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEmployee $employee
 * @property ScientificPlatform $scientificPlatform
 * @property EducationYear $educationYear
 */
class EScientificPlatformProfile extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    const SCENARIO_CREATE_AUTHOR = 'create_author';
    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return 'e_scientific_platform_profile';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getEmployeeCheckedProfileList($education_year = false, $teacher = "")
    {
        return self::find()
            //->select('e_publication_author_meta.id, e_publication_author_meta._employee, e_publication_methodical.name as work_name, e_publication_methodical._methodical_publication_type as _methodical_publication_type, e_publication_methodical._publication_database')
            //->joinWith(['publicationMethodical'])
            ->where([
                'is_checked' => self::STATUS_ENABLE,
                'active' => self::STATUS_ENABLE,
                '_education_year' => $education_year,
            ])
            ->andWhere(['in', '_employee', $teacher])
            ->all();
    }

    public static function getCheckedPlatform($education_year = false, $faculty = "", $department = "", $teacher = "")
    {
        if ($faculty != "" && $department == "") {
            $department_list = EDepartment::getDepartmentList($faculty);
            $departments = array();
            foreach ($department_list as $item) {
                $departments[$item->id] = $item->id;
            }
            $teacher_list = EEmployeeMeta::getTeacherList($departments);
            $teachers = array();
            foreach ($teacher_list as $item) {
                $teachers[$item->_employee] = $item->_employee;
            }
        } elseif ($department != "") {
            $departments = $department;
            $teacher_list = EEmployeeMeta::getTeacherList($departments);
            $teachers = array();
            foreach ($teacher_list as $item) {
                $teachers[$item->_employee] = $item->_employee;
            }
        } elseif ($teacher != "") {
            $teachers = $teacher;
        }

        if ($faculty != "" || $department != "" || $teacher != "") {
            return self::find()
                ->select('_employee, _scientific_platform, h_index, publication_work_count, citation_count')
                ->where([
                    'is_checked' => self::STATUS_ENABLE,
                    'active' => self::STATUS_ENABLE,
                    '_education_year' => $education_year,
                ])
                ->andWhere(['in', '_employee', $teachers])
                //->groupBy(['_employee', '_scientific_platform', 'h_index', 'publication_work_count', 'citation_count'])
                ->all();
        } else {
            return self::find()
                //->select('_employee, _methodical_publication_type, COUNT(e_publication_methodical._methodical_publication_type) as methodical_publication_types, , e_publication_methodical._publication_database')
                ->select('_employee, _scientific_platform, h_index, publication_work_count, citation_count')
                ->where([
                    'is_checked' => self::STATUS_ENABLE,
                    'active' => self::STATUS_ENABLE,
                    '_education_year' => $education_year,
                ])
                //->groupBy(['_employee', '_methodical_publication_type', '_publication_database'])
                ->all();
        }

    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_scientific_platform', 'profile_link', 'h_index', 'publication_work_count', 'citation_count', '_education_year'], 'required', 'on' => self::SCENARIO_CREATE_AUTHOR],
            [['_employee', 'h_index', 'publication_work_count', 'citation_count', 'position'], 'default', 'value' => null],
            [['_employee', 'h_index', 'publication_work_count', 'citation_count', 'position'], 'integer'],
            [['is_checked', 'active'], 'boolean'],
            [['is_checked_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['_scientific_platform', '_education_year'], 'string', 'max' => 64],
            [['profile_link'], 'string', 'max' => 512],
            [['_employee', '_scientific_platform', '_education_year'], 'unique', 'targetAttribute' => ['_employee', '_scientific_platform', '_education_year']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_scientific_platform'], 'exist', 'skipOnError' => true, 'targetClass' => ScientificPlatform::className(), 'targetAttribute' => ['_scientific_platform' => 'code']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
        ]);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getScientificPlatform()
    {
        return $this->hasOne(ScientificPlatform::className(), ['code' => '_scientific_platform']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
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
                    'profile_link',
                    'h_index',
                    'publication_work_count',
                    'citation_count',
                    '_scientific_platform',
                    '_education_year',
                    '_employee',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('profile_link', $this->search);
            $query->orWhereLike('authors', $this->search);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_scientific_platform) {
            $query->andFilterWhere(['_scientific_platform' => $this->_scientific_platform]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }

        return $dataProvider;
    }

    public function search_department($params, $department)
    {
        $this->load($params);
        $list = array();
        $teachers = EEmployeeMeta::getTeacherList($department);
        foreach ($teachers as $item) {
            $list [$item->_employee] = $item->_employee;
        }
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'id',
                    'profile_link',
                    'h_index',
                    'publication_work_count',
                    'citation_count',
                    '_scientific_platform',
                    '_education_year',
                    '_employee',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('profile_link', $this->search);
            $query->orWhereLike('authors', $this->search);
        }
        $query->andFilterWhere(['in', '_employee', $list]);
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_scientific_platform) {
            $query->andFilterWhere(['_scientific_platform' => $this->_scientific_platform]);
        }
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf('%s / %s / %s', $this->employee->getShortName(), $this->educationYear->name, $this->scientificPlatform->name);
    }


}
