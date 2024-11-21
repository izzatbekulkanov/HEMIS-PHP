<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use common\models\system\SystemMessageTranslation;
use DateInterval;
use DateTime;
use frontend\models\curriculum\SubjectResource;
use frontend\models\system\Student;
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
use yii\rbac\Role;

/**
 * This is the model class for table "e_subject_task".
 *
 * @property int $id
 * @property string $name
 * @property string $comment
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_education_year
 * @property string $_semester
 * @property string $_exam_type
 * @property int $_employee
 * @property int $max_ball
 * @property int $attempts
 * @property int $question_count
 * @property int $duration
 * @property bool $active
 * @property bool $random
 * @property DateTime $updated_at
 * @property DateTime $created_at
 * @property DateTime $start_at
 * @property DateTime $finish_at
 *
 * @property ECurriculum $curriculum
 * @property EEmployee $employee
 * @property ESubject $subject
 * @property EducationYear $educationYear
 * @property ExamType $examType
 * @property EExamStudent[] $examStudents
 * @property EGroup[] $groups
 * @property EExamGroup[] $examGroups
 * @property EExamQuestion[] $testQuestions
 * @property EExamQuestion[] $activeTestQuestions
 * @property EExamStudent[] $examStudentResults
 */
class EExam extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    public $_department;
    public $_user;

    public static function tableName()
    {
        return 'e_exam';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enabled'),
            self::STATUS_DISABLE => __('Disabled'),
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Education Curriculum')
        ]);
    }

    public static $_exam_type_options;

    public static function getExamTypeOptions()
    {
        if (self::$_exam_type_options == null) {
            $items = ExamType::find()
                ->where(['active' => true, 'code' => [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]])
                ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all();

            self::$_exam_type_options = ArrayHelper::map($items, 'code', 'name');
        }

        return self::$_exam_type_options;
    }

    public function getExamTypeLabel()
    {
        $options = self::getExamTypeOptions();

        return @$options[$this->_exam_type];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'comment', 'max_ball', 'start_at', 'finish_at', 'duration', 'attempts', 'random', '_exam_type', 'question_count', '_curriculum', '_subject'], 'required'],
            [['comment'], 'string', 'max' => 4000],
            [['_department'], 'safe'],
            [['duration'], 'validateDate'],
            [['name'], 'string', 'max' => 512],
            [['max_ball'], 'validateMaxBall'],
            [['active', 'random'], 'boolean'],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_exam_type'], 'in', 'range' => array_keys(self::getExamTypeOptions())],
        ]);
    }

    public function validateMaxBall()
    {
        if ($max = $this->getFinalExamInfo()) {
            $max = $max->max_ball;
            $min = $this->curriculum->markingSystem->isFiveMarkSystem() ? $max : 0;
            if ($this->max_ball < $min) {
                $this->addError('max_ball', Yii::t('yii', '{attribute} must be no less than {min}.', ['min' => $min, 'attribute' => $this->getAttributeLabel('max_ball')]));
            } else if ($this->max_ball > $max) {
                $this->addError('max_ball', Yii::t('yii', '{attribute} must be no greater than {max}.', ['max' => $max, 'attribute' => $this->getAttributeLabel('max_ball')]));
            }
        }
    }

    public function getFinalExamInfo()
    {
        return ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($this->_curriculum, $this->_semester, $this->_subject, ExamType::EXAM_TYPE_FINAL);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getExamType()
    {
        return $this->hasOne(ExamType::className(), ['code' => '_exam_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getExamStudents()
    {
        return $this->hasMany(EExamStudent::className(), ['_exam' => 'id']);
    }

    public function getExamGroups()
    {
        return $this->hasMany(EExamGroup::className(), ['_exam' => 'id']);
    }

    public function getGroups()
    {
        return $this->hasMany(EGroup::className(), ['id' => '_group'])
            ->viaTable('e_exam_group', ['_exam' => 'id']);
    }

    public function getGroupsLabel()
    {
        return implode(', ', ArrayHelper::getColumn(array_slice($this->groups, 0, 6), 'name'));
    }

    public function getTestQuestions()
    {
        return $this->hasMany(EExamQuestion::className(), ['_exam' => 'id']);
    }

    public function getExamStudentResults()
    {
        return $this->hasMany(EExamStudent::className(), ['_exam' => 'id'])->andFilterWhere(['>', 'e_exam_student.attempts', 0]);
    }

    public function getActiveTestQuestions()
    {
        return $this->hasMany(EExamQuestion::className(), ['_exam' => 'id'])->where(['active' => true]);
    }

    public function validateDate($attribute, $options)
    {
        $start = false;
        $end = false;

        if (is_string($this->start_at)) {
            if ($date = date_create_from_format('Y-m-d H:i', $this->start_at, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $start = $date;
            } else {
                $this->addError('start_at', __('Invalid date format'));
            }
        }
        if (is_string($this->finish_at)) {
            if ($date = date_create_from_format('Y-m-d H:i', $this->finish_at, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $end = $date;
            } else {
                $this->addError('finish_at', __('Invalid date format'));
            }
        }
        if ($start && $end) {
            if ($start->getTimestamp() >= $end->getTimestamp()) {
                $this->addError('finish_at', __('Tugash vaqti boshlanish vaqtidan katta bo\'lishi kerak'));
            }
            if ($start->getTimestamp() + $this->duration * 60 >= $end->getTimestamp() + 1) {
                $this->addError('duration', __('Test davomiyligi boshlanish va tugash vaqtiga mos emas'));
            }
        }
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->_education_year = EducationYear::getCurrentYear()->code;
        }

        if (is_string($this->start_at)) {
            if ($date = date_create_from_format('Y-m-d H:i', $this->start_at, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->start_at = $date;
            }
        }
        if (is_string($this->finish_at)) {
            if ($date = date_create_from_format('Y-m-d H:i', $this->finish_at, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->finish_at = $date;
            }
        }

        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        if (!$this->canEditExam()) {
            throw new \Exception(__('Faol holatdagi imtihonni o\'chirish mumkin emas'));
        }
        return parent::beforeDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->reIndexStudents();

        parent::afterSave($insert, $changedAttributes);
    }

    public function searchGroups()
    {
        return new ActiveDataProvider([
            'query' => $this
                ->getExamGroups()
                ->leftJoin('e_group', 'e_group.id=_group')
                ->orderBy(['e_group.name' => SORT_ASC]),

            'pagination' => [
                'pageSize' => 400,
            ],
        ]);
    }

    public function searchForEmployee($params, Admin $admin)
    {
        $this->load($params);

        $query = self::find()
            ->with(['subject', 'examType', 'educationYear', 'groups', 'employee']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['_education_year' => SORT_DESC, '_semester' => SORT_DESC, 'created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    '_curriculum',
                    '_subject',
                    '_employee',
                    '_semester',
                    '_education_year',
                    '_exam_type',
                    'name',
                    'start_at',
                    'duration',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name', $this->search);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }

        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }

        if ($admin->role->isAcademic() || $admin->role->isSuperAdminRole()) {

        } else {
            $query->andFilterWhere(['_employee' => $admin->_employee]);
        }

        return $dataProvider;
    }

    public function searchForSubjectAndGroup($params, ESubjectExamSchedule $examSchedule, $type = ExamType::EXAM_TYPE_FINAL)
    {
        $this->load($params);

        $query = self::find()
            ->leftJoin('e_exam_group', 'e_exam_group._exam=e_exam.id and e_exam_group._group=:group', ['group' => $examSchedule->_group])
            ->with(['subject', 'examType', 'educationYear', 'employee']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['_education_year' => SORT_DESC, '_semester' => SORT_DESC, 'created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    '_curriculum',
                    '_subject',
                    '_employee',
                    '_semester',
                    '_education_year',
                    '_exam_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name', $this->search);
        }

        $query->andFilterWhere(['_subject' => $examSchedule->_subject]);
        $query->andFilterWhere(['e_exam_group._group' => $examSchedule->_group]);
        $query->andFilterWhere(['_exam_type' => $type]);
        $query->andFilterWhere(['_education_year' => $this->_education_year]);


        return $dataProvider;
    }

    public function canStartTest()
    {
        if ($this->active && $this->getActiveTestQuestions()->count() > 0) {
            /*$now = time();

            if ($this->start_at->getTimestamp() <= $now && $now <= $this->finish_at->getTimestamp()) {
                return true;
            }*/

            return true;
        }

        return false;
    }

    /**
     * @param EStudent $student
     * @return EExamGroup | null
     */
    public function getStudentExamGroup(EStudent $student)
    {
        return $this->getExamGroups()->where(['_group' => $student->getGroupIds()])->one();
    }

    public function canJoinExam(EStudent $student)
    {
        if ($this->canStartTest()) {
            if (EExamExclude::find()->where(['_exam' => $this->id, '_student' => $student->id])->count()) {
                return false;
            }

            if ($group = $this->getStudentExamGroup($student)) {
                $now = time();
                $end = $now + $this->duration * 60;

                if ($group->getStartAtTime()->getTimestamp() <= $now && $now <= $group->getFinishAtTime()->getTimestamp()) {
                    if ($studentExam = $this->getStudentExam($student, false)) {
                        if ($studentExam->finished_at) {
                            return $studentExam->attempts < $this->attempts;
                        } else {
                            $testTime = $studentExam->getHowMuchTime();

                            return true;
                        }
                    }
                    return true;
                }
            }
        }

        return false;
    }


    protected function reIndexStudents()
    {

    }


    public function removeGroups($items)
    {
        $success = 0;
        if (is_array($items) && count($items)) {
            foreach ($items as $id) {
                if (EExamGroup::deleteAll(['_group' => $id, '_exam' => $this->id])) {
                    $success++;
                }
            }
        }

        return $success;
    }

    public function addGroups($items)
    {
        $success = 0;
        if (is_array($items) && count($items)) {
            try {
                $inserts = [];
                foreach ($items as $id) {
                    if ($group = EGroup::findOne($id)) {
                        $key = ['_exam' => $this->id, '_group' => $id];
                        if (EExamGroup::findOne($key) == null) {
                            $inserts[] = $key;
                        }
                    }
                }
                if (count($inserts)) {
                    return Yii::$app->db
                        ->createCommand()
                        ->batchInsert(EExamGroup::tableName(), ['_exam', '_group'], $inserts)
                        ->execute();
                }
            } catch (\Exception $e) {
            }
        }

        return $success;
    }

    public function getCurriculumOptions(Admin $admin, $educationYear)
    {
        $ids = ESubjectExamSchedule::find()
            ->with(['curriculum'])
            ->select(['_curriculum'])
            ->where([
                '_education_year' => $educationYear
            ]);

        if ($admin->role->isTeacherRole()) {
            $ids->andFilterWhere(['_employee' => $admin->_employee]);
        }

        $ids->groupBy(['_curriculum']);
        return ArrayHelper::map($ids->all(), '_curriculum', 'curriculum.name');
    }

    public function getSubjectOptions(Admin $admin, $curriculum = false)
    {
        $ids = ESubjectExamSchedule::find()
            ->select(['_subject'])
            ->andFilterWhere(['_curriculum' => $curriculum]);

        if ($admin->role->isTeacherRole()) {
            $ids->andFilterWhere(['_employee' => $admin->_employee]);
        }

        $ids->groupBy(['_subject']);

        return ArrayHelper::map($ids->all(), '_subject', 'subject.name');
    }

    public function getGroupsProvider($params, Admin $admin)
    {
        $this->load($params);

        $query = EGroup::find();

        if ($this->search) {
            $query->orWhereLike('e_group.name', $this->search);
        }

        $query->andFilterWhere(['active' => true]);


        if ($admin->role->isTeacherRole()) {
            $ids = ESubjectSchedule::find()
                ->select(['_group'])
                ->where([
                    '_subject' => $this->_subject,
                    '_education_year' => $this->_education_year ? $this->_education_year : EducationYear::getCurrentYear()->code
                ]);

            $ids->andFilterWhere(['_employee' => $admin->_employee]);

            $query->andFilterWhere(['id' => $ids->distinct()]);
        } else if ($admin->role->isDeanRole()) {
            if ($department = $admin->employee->deanFaculties) {
                $query->andFilterWhere(['_department' => $department->id]);
            }
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }

        return new ActiveDataProvider([
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
    }


    public function canEditExam()
    {
        /**
         * @var $role AdminRole
         * @var $employee EEmployee
         */

        if ($this->isNewRecord) {
            return true;
        }
        if ($this->active || $this->_education_year != EducationYear::getCurrentYear()->code) {
            return false;
        }

        if (Yii::$app->user->identity && Yii::$app->user->identity->role) {
            $role = Yii::$app->user->identity->role;
            if ($role->isSuperAdminRole() || $role->isAcademic()) {
                return true;
            }

            return $this->_employee == Yii::$app->user->identity->_employee;
        }

        return false;
    }


    /**
     * @param Student $student
     * @return EExamStudent
     */
    public function getStudentExam(EStudent $student, $create = false, $session = '')
    {
        $data = ['_exam' => $this->id, '_student' => $student->id, '_group' => $student->meta->_group];

        if ($exam = EExamStudent::findOne($data)) {
            if ($exam->session == null) {
                $exam->updateAttributes(['session' => $session]);
            }

            return $exam;
        } elseif ($create) {
            $exam = new EExamStudent($data);
            $exam->session = $session;

            $exam->save(false);
            $exam->refresh();

            return $exam;
        }
    }
}
