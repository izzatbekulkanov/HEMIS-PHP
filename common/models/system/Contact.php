<?php

namespace common\models\system;

use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectSchedule;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\classifier\StructureType;
use DateTime;
use frontend\models\system\Student;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 *
 * @property int $_admin
 * @property int $_student
 * @property int $_student_department
 * @property int $_department
 * @property int $_group
 * @property int $_role
 * @property int $_employee
 * @property boolean active
 * @property string type
 * @property string name
 * @property string label
 * @property string[] $_translations
 *
 * @property Admin $admin
 * @property Student $student
 * @property EEmployee $employee
 */
class Contact extends _BaseModel
{
    public const TYPE_ADMIN = 'admin';
    public const TYPE_STUDENT = 'student';
    public const TYPE_ROLE = 'role';
    public const TYPE_GROUP = 'group';
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_STUDENT_DEPARTMENT = 'student_department';

    public static function tableName()
    {
        return 'e_admin_message_contact';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['type', '_admin', '_student', '_department', '_group', '_role', '_student_department', 'search'], 'safe']
        ]);
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => '_admin']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::class, ['id' => '_employee']);
    }

    public function getStudent()
    {
        return $this->hasOne(Student::class, ['id' => '_student']);
    }

    public function getFullName()
    {
        return $this->name;
    }


    public static function getSelected($selected = [])
    {
        $result = [];
        if (count($selected)) {
            return ArrayHelper::getColumn(Contact::find()
                ->where(['id' => array_filter($selected)])
                ->all(), function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            });
        }
        return $result;
    }

    public function searchForAdmin($params, IdentityInterface $userIdentity, $asProvider = true)
    {
        $this->load($params);

        $query = self::find()->with(['admin', 'employee']);

        if ($this->search) {
            $query->orWhereLike('name', $this->search);
            $query->orWhereLike('label', $this->search);
        }

        $query->andFilterWhere(['type' => self::TYPE_ADMIN]);

        if ($this->_department) {
            $employees = EEmployeeMeta::find()
                ->select(['_employee'])
                ->where(['_department' => $this->_department])
                ->column();

            $employees[] = -1;

            $query->andFilterWhere(['in', '_employee', $employees]);
        }


        if ($userIdentity instanceof Student) {
            $year = EducationYear::getCurrentYear();

            $deps = EStudentMeta::find()
                ->select(['_department'])
                ->where(['_student' => $userIdentity->id])
                ->andFilterWhere(['_education_year' => $year->code])
                ->column();

            $employees = EEmployeeMeta::find()
                ->select(['_employee'])
                ->where(['_department' => $deps])
                ->column();

            $groups = $userIdentity->getGroupIds();

            $teachers = ESubjectSchedule::find()
                ->select(['_employee'])
                ->where(['_group' => $groups])
                ->andFilterWhere(['_education_year' => $year->code])
                ->column();

            $employees = array_unique(array_merge($teachers, $employees));

            $employees[] = -1;

            $query->andFilterWhere(['in', '_employee', $employees]);
        }

        if ($asProvider) {
            return new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['name' => SORT_ASC],
                    'attributes' => $this->attributes()
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        }

        $result = [];
        foreach ($query->all() as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'label' => $item->label,
            ];
        }

        return $result;
    }


    public function searchForStudent($params, IdentityInterface $userIdentity, $asProvider = true)
    {
        $this->load($params);

        $query = self::find()->with(['student']);

        if ($this->search) {
            $query->orWhereLike('name', $this->search);
            $query->orWhereLike('label', $this->search);
        }

        $query->andFilterWhere(['type' => self::TYPE_STUDENT]);

        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }

        if ($asProvider) {
            return new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['_student_department' => SORT_ASC, '_group' => SORT_ASC, 'name' => SORT_ASC],
                    'attributes' => $this->attributes()
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        }

        $result = [];
        foreach ($query->all() as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }

        return $result;
    }

    public static function indexAdmins(Admin $admin = null)
    {
        /**
         * @var $admin Admin
         * @var $meta EEmployeeMeta
         */
        echo "Index admin contacts\n";

        if ($admin) {
            $admins = [$admin];
        } else {
            $admins = Admin::find()->with(['employee'])->all();
        }

        $ids = [];
        foreach ($admins as $admin) {
            $ids[] = $admin->id;

            $data = [
                'name' => $admin->full_name,
                '_admin' => $admin->id,
                '_employee' => $admin->_employee,
                'type' => self::TYPE_ADMIN,
                'active' => $admin->status == Admin::STATUS_ENABLE,
            ];

            if ($admin->_employee && count($admin->employee->departments)) {
                $department = $admin->employee->departments[0];
                $data['label'] = $department->name . " ({$department->structureType->name})";
            } else {
                if ($admin->role) {
                    $data['label'] = $admin->role->name;
                }
            }

            if ($admin->_employee && false) {
                $departments = EEmployeeMeta::find()
                    ->with(['department', 'position'])
                    ->where(['_employee' => $admin->employee, 'active' => true])
                    ->all();
                foreach ($departments as $meta) {
                    $data['_department'] = $meta->_department;
                    $data['_position'] = $meta->_position;

                    echo Yii::$app->db
                        ->createCommand()
                        ->upsert(self::tableName(), $data)
                        ->execute();
                }
            } else {
                echo Yii::$app->db
                    ->createCommand()
                    ->upsert(self::tableName(), $data)
                    ->execute();
            }

        }
    }

    public static function indexStudents(EStudent $student = null)
    {
        /**
         * @var $student EStudent
         * @var $meta EEmployeeMeta
         */
        echo "Index student contacts\n";

        if ($student) {
            $students = [$student];
        } else {
            $students = Student::find()
                ->with(['groups'])
                ->all();
        }


        $ids = [];
        foreach ($students as $student) {
            $ids[] = $student->id;

            $data = [
                'name' => $student->getFullName(),
                '_student' => $student->id,
                'type' => self::TYPE_STUDENT,
                'active' => true,
            ];

            if ($student->groups && count($student->groups)) {
                $group = $student->groups[0];
                $department = $group->department;

                $data['label'] = $group->name . " ({$department->name})";
                $data['_group'] = $group->id;
                $data['_student_department'] = $department->id;

                echo Yii::$app->db
                    ->createCommand()
                    ->upsert(self::tableName(), $data)
                    ->execute();
            } else {
                echo Yii::$app->db
                    ->createCommand()
                    ->upsert(self::tableName(), $data)
                    ->execute();
            }
        }

    }

    public function getLabel()
    {
        return $this->label;
    }
}
