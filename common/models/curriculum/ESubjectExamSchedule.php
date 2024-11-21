<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\infrastructure\EAuditorium;
use common\models\student\EGroup;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
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
 * This is the model class for table "e_subject_exam_schedule".
 *
 * @property int $id
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_education_year
 * @property string $_semester
 * @property int $_group
 * @property string $_exam_type
 * @property int $_auditorium
 * @property int $_week
 * @property int $_employee
 * @property string $_lesson_pair
 * @property string $exam_name
 * @property DateTime $exam_date
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EAuditorium $auditorium
 * @property ECurriculum $curriculum
 * @property EGroup $group
 * @property ESubject $subject
 * @property LessonPair $lessonPair
 * @property EEmployee $employee
 * @property HEducationYear $educationYear
 * @property HExamType $examType
 */
class ESubjectExamSchedule extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    protected $_translatedAttributes = ['name'];
    public $groups;
    public $minimum_limit;
    public $_department;

    public static function tableName()
    {
        return 'e_subject_exam_schedule';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getExamByCurriculumSubject($subject = false, $curriculum = false, $semester = false, $group = false)
    {
        return self::find()
            ->where([
                '_subject' => $subject,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            ->count();
    }

    public static function getExamByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_subject' => $subject,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                'active' => self::STATUS_ENABLE
            ])
            ->count();
    }

    public static function getExamByCurriculumSubjectType($subject = false, $curriculum = false, $semester = false, $group = false, $exam_type = false)
    {
        return self::find()
            ->where([
                '_subject' => $subject,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_group' => $group,
                '_exam_type' => $exam_type,
                'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public static function getFinalExamByCurriculumSubjectType($subject = false, $curriculum = false, $semester = false, $group = false, $exam_type = false, $final_exam_type = false)
    {
        return self::find()
            ->where([
                '_subject' => $subject,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_group' => $group,
                '_exam_type' => $exam_type,
                'final_exam_type' => $final_exam_type,
                'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public static function getMidtermExamCount($data = "")
    {
        $query = self::find()
            ->where([
                '_education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                'active' => self::STATUS_ENABLE
            ])
            ->andFilterWhere(['not in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);
        $query = ($data == "") ? $query->count() : $query->all();
        return $query;
    }

    public static function getFinalExamCount($data = "")
    {
        $query = self::find()
            ->where([
                '_education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                'active' => self::STATUS_ENABLE
            ])
            ->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_FINAL]]);
        $query = ($data == "") ? $query->count() : $query->all();
        return $query;
    }

    public static function getOverallExamCount($data = "")
    {
        $query = self::find()
            ->where([
                '_education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                'active' => self::STATUS_ENABLE
            ])
            ->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]]);
        $query = ($data == "") ? $query->count() : $query->all();
        return $query;
    }

    public static function getGeneralExam()
    {
        $query = self::find()
            ->where([
                //'_education_year' => EducationYear::getCurrentYear()->code,
                // '_employee'=>Yii::$app->user->identity->_employee,
                'active' => self::STATUS_ENABLE
            ])
            ->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);
        $query = $query->all();
        return $query;
    }
    public static function getTeachersByCurriculumSemesterSubjectExam($curriculum = false, $semester = false, $subject = false, $exam_type)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_exam_type' => $exam_type,
                'active' => self::STATUS_ENABLE
            ])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_subject', 'groups', '_exam_type', '_auditorium', '_employee', '_lesson_pair', 'exam_date', 'final_exam_type'], 'required', 'on' => self::SCENARIO_INSERT],
            [['_curriculum', '_subject', '_group', '_auditorium', '_week', '_employee', 'position', 'final_exam_type'], 'default', 'value' => null],
            [['_curriculum', '_subject', '_group', '_auditorium', '_week', '_employee', 'position', '_department'], 'integer'],
            [['exam_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_year', '_semester', '_exam_type', '_lesson_pair', 'exam_name', 'final_exam_type'], 'string', 'max' => 64],
            [['exam_date', '_auditorium', '_lesson_pair', '_exam_type', '_group'], 'unique', 'targetAttribute' => ['exam_date', '_auditorium', '_lesson_pair', '_exam_type', '_group']],
            //[['exam_date', '_employee', '_lesson_pair'], 'unique', 'targetAttribute' => ['exam_date', '_employee', '_lesson_pair']],

            [['_auditorium'], 'exist', 'skipOnError' => true, 'targetClass' => EAuditorium::className(), 'targetAttribute' => ['_auditorium' => 'code']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => ExamType::className(), 'targetAttribute' => ['_exam_type' => 'code']],
            [['final_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => FinalExamType::className(), 'targetAttribute' => ['final_exam_type' => 'code']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],

        ]);
    }

    public function getAuditorium()
    {
        return $this->hasOne(EAuditorium::className(), ['code' => '_auditorium']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
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

    public function getFinalExamType()
    {
        return $this->hasOne(FinalExamType::className(), ['code' => 'final_exam_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getLessonPair()
    {
        return $this->hasOne(LessonPair::className(), ['code' => '_lesson_pair', '_education_year' => '_education_year']);
    }

    public function getCurriculumSubject()
    {
        return $this->hasOne(ECurriculumSubject::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['curriculum']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_education_year',
                    '_semester',
                    '_subject',
                    'exam_date',
                    '_lesson_pair',
                    '_group',
                    'final_exam_type',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_exam_schedule._education_year' => $this->_education_year]);
        }

        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->final_exam_type) {
            $query->andFilterWhere(['final_exam_type' => $this->final_exam_type]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        return $dataProvider;
    }

    public function search_performance($params)
    {
        $this->load($params);
        /*if ($this->_education_year == null) {
            $this->_semester = null;
        }*/
        if ($this->_curriculum == null) {
            $this->_group = null;
            $this->_subject = null;
            $this->_semester = null;
        }
        /*if ($this->_department == null) {
            $this->_curriculum = null;

        }*/
        $query = self::find();
        $query->joinWith(['curriculum']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_education_year',
                    '_semester',
                    '_subject',
                    'exam_date',
                    '_exam_type',
                    '_lesson_pair',
                    '_group',
                    'final_exam_type',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_exam_schedule._education_year' => $this->_education_year]);
        }

        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }

        if ($this->final_exam_type) {
            $query->andFilterWhere(['final_exam_type' => $this->final_exam_type]);
        }
        return $dataProvider;
    }

    private function getSelectQueryFilters($col)
    {
        $query = self::find()->select([$col])
            ->andFilterWhere(['active' => true])
            ->distinct();

        foreach (['_education_year', '_semester', '_group', '_curriculum', '_subject', '_exam_type'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    private function getSelectEmployeeQueryFilters($col, $type)
    {
        $query = self::find()->joinWith(['curriculumSubject'])->select(['e_subject_exam_schedule.' . $col])
            ->andFilterWhere(['e_subject_exam_schedule.active' => true])
            ->andFilterWhere(['e_subject_exam_schedule._employee' => Yii::$app->user->identity->_employee])
            ->distinct();
        if ($type == 'midterm') {
            $query->andFilterWhere(['not in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);
        } elseif ($type == 'final') {
            $query->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);
            $query->andFilterWhere(['in', 'e_curriculum_subject._rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]]);
        } elseif ($type == 'other') {
            $query->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]]);
            $query->andFilterWhere(['not in', 'e_curriculum_subject._rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]]);
        }
        foreach (['_education_year', '_semester', '_group', '_curriculum', '_subject', '_exam_type', 'final_exam_type'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere(['e_subject_exam_schedule.' . $attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    public function getEducationYearItems()
    {
        return ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_year')])
                ->all(), 'code', 'name');
    }

    public function getCurriculumItems($faculty)
    {
        if ($faculty) {
            return ArrayHelper::map(
                ECurriculum::find()
                    ->orderBy(['name' => SORT_ASC])
                    ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_curriculum'), '_department' => $faculty])
                    ->all(), 'id', 'name');
        }
    }

    public function getSemesterItems()
    {
        return ArrayHelper::map(
            Semester::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_semester'), '_curriculum' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), 'code', 'name');
    }

    public function getGroupItems(Admin $admin)
    {
        $query = EGroup::find()
            ->orderBy(['name' => SORT_ASC])
            ->where([
                'active' => true,
                'id' => $this->getSelectQueryFilters('_group'),
                '_curriculum' => $this->getSelectQueryFilters('_curriculum')
            ]);

        if (count($admin->tutorGroups)) {
            $query->andFilterWhere(['id' => array_keys($admin->tutorGroups)]);
        }

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function getSubjectItems()
    {
        return ArrayHelper::map(
            ECurriculumSubject::find()
                //->orderBy(['subject.name' => SORT_ASC])
                ->where(['active' => true, '_subject' => $this->getSelectQueryFilters('_subject'), '_semester' => $this->getSelectQueryFilters('_semester'), '_curriculum' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), '_subject', 'subject.name');
    }

    public function getExamTypeItems()
    {
        return ArrayHelper::map(
            ECurriculumSubjectExamType::find()
                //->orderBy(['subject.name' => SORT_ASC])
                ->where(['active' => true, '_exam_type' => $this->getSelectQueryFilters('_exam_type'), '_subject' => $this->getSelectQueryFilters('_subject'), '_semester' => $this->getSelectQueryFilters('_semester'), '_curriculum' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), '_exam_type', 'examType.name');
    }

    public function getEmployeeEducationYearItems($type)
    {
        return ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectEmployeeQueryFilters('_education_year', $type)])
                ->all(), 'code', 'name');
    }

    public function getEmployeeSemesterItems($type)
    {
        return ArrayHelper::map(
            Semester::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectEmployeeQueryFilters('_semester', $type), '_education_year' => $this->getSelectEmployeeQueryFilters('_education_year', $type)])
                ->all(), 'code', 'name');
    }

    public function getEmployeeGroupItems($type)
    {
        return ArrayHelper::map(
            EGroup::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => $this->getSelectEmployeeQueryFilters('_group', $type)])
                ->all(), 'id', 'name');
    }

    public function getEmployeeSubjectItems($type)
    {
        return ArrayHelper::map(
            ECurriculumSubject::find()
                //->orderBy(['subject.name' => SORT_ASC])
                ->where(['active' => true, '_subject' => $this->getSelectEmployeeQueryFilters('_subject', $type), '_semester' => $this->getSelectEmployeeQueryFilters('_semester', $type)])
                ->all(), '_subject', 'subject.name');
    }


    public function getEmployeeFinalExamTypeItems($type)
    {
        return ArrayHelper::map(
            FinalExamType::find()
                //->orderBy(['subject.name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectEmployeeQueryFilters('final_exam_type', $type)])
                ->all(), 'code', 'name');
    }

    public function search_teacher($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['curriculum', 'curriculumSubject']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_education_year',
                    'e_subject_exam_schedule._semester',
                    '_subject',
                    'exam_date',
                    '_lesson_pair',
                    '_group',
                    'final_exam_type',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_exam_schedule._education_year' => $this->_education_year]);
        }

        if ($this->_semester) {
            $query->andFilterWhere(['e_subject_exam_schedule._semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['e_subject_exam_schedule._subject' => $this->_subject]);
        }
        if ($this->final_exam_type) {
            $query->andFilterWhere(['final_exam_type' => $this->final_exam_type]);
        }
        return $dataProvider;
    }
}
