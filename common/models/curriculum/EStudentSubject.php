<?php

namespace common\models\curriculum;

use common\models\archive\EAcademicRecord;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\curriculum\Semester;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ESubject;
use common\models\system\classifier\StudentStatus;
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
 * This is the model class for table "e_student_subject".
 *
 * @property int $id
 * @property int $_curriculum
 * @property int $_subject
 * @property int $_student
 * @property int $_group
 * @property string $_education_year
 * @property string $_semester
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EGroup $group
 * @property EStudent $student
 * @property ESubject $subject
 * @property ECurriculumSubject $curriculumSubject
 * @property ECurriculumSubjectExamType $curriculumSubjectExamType
 * @property EAcademicRecord $academicRecord
 * @property EducationYear $educationYear
 */
class EStudentSubject extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SCENARIO_EDIT_GROUP = 'edit_group';
    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_student_subject';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getSubjectByCurriculumSemesterGroup($curriculum = false, $education_year = false, $semester = false, $group = false)
    {
        $subjects = ECurriculumSubject::find()
            ->select(['_subject'])
            ->where(['active' => self::STATUS_ENABLE, '_curriculum' => $curriculum, '_semester' => $semester])
            ->column();
        return self::find()
            ->select('_subject, _group')
            ->where([
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
                '_semester' => $semester,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['in', '_subject', $subjects])
            ->groupBy(['_subject', '_group'])
            //    ->orderByTranslationField('position')
            ->all();
    }

    public static function getStudentsByYearSemesterGroup($curriculum = false, $education_year = false, $semester = false, $subject = false, $group = false)
    {
        $query = self::find();
        $query->leftJoin('e_student', 'e_student.id=e_student_subject._student');
        $query->leftJoin('e_student_meta', 'e_student_meta._student=e_student_subject._student');
        $query->with(['student', 'studentMeta']);
        $query->where([
            'e_student_subject._curriculum' => $curriculum,
            'e_student_subject._education_year' => $education_year,
            '_semester' => $semester,
            '_subject' => $subject,
            'e_student_meta._group' => $group,
            'e_student_subject.active' => self::STATUS_ENABLE,
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            'e_student_meta._semestr' => $semester,
            'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,

        ]);
        $query->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC]);
        $query = $query->all();
        return $query;
    }

    public static function getStudentsByYearSemesterGroupPerformance($curriculum = false, $education_year = false, $semester = false, $subject = false, $group = false)
    {
        $query = self::find();
        $query->leftJoin('e_student', 'e_student.id=e_student_subject._student');
        $query->leftJoin('e_student_meta', 'e_student_meta._student=e_student_subject._student');
        $query->with(['student', 'studentMeta']);
        $query->where([
            'e_student_subject._curriculum' => $curriculum,
            'e_student_subject._education_year' => $education_year,
            '_semester' => $semester,
            '_subject' => $subject,
            'e_student_meta._group' => $group,
            'e_student_subject.active' => self::STATUS_ENABLE,
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            'e_student_meta._semestr' => $semester,
        ]);
        $query->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC]);
        $query = $query->all();
        return $query;
    }

    public static function getStudentIdsByYearSemesterGroup($curriculum = false, $education_year = false, $semester = false, $group = false)
    {
        $query = self::find();
        $query->select('e_student_subject._student, e_student.second_name, e_student.first_name, e_student.third_name');
        $query->leftJoin('e_student', 'e_student.id=e_student_subject._student');
        $query->leftJoin('e_student_meta', 'e_student_meta._student=e_student_subject._student AND e_student_meta._semestr=e_student_subject._semester AND e_student_meta._education_year=e_student_subject._education_year');
        $query->with(['student']);
        $query->where([
            'e_student_subject._curriculum' => $curriculum,
            'e_student_subject._education_year' => $education_year,
            '_semester' => $semester,
            'e_student_subject._group' => $group,
            'e_student_subject.active' => self::STATUS_ENABLE,
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,

        ]);

        $query->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC]);
        $query->groupBy(['e_student_subject._student', 'e_student.second_name', 'e_student.first_name', 'e_student.third_name']);
        $query = $query->all();
        return $query;
    }

    /*public static function getStudentIdsByYearSemesterGroup($curriculum = false, $education_year = false, $semester = false, $group = false)
    {
        return self::find()
            ->select('_student')
            ->where([
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
                '_semester' => $semester,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            ->groupBy(['_student'])
            ->all();
    }*/

    public static function getSubjectIdsByYearSemesterGroup($curriculum = false, $education_year = false, $semester = false, $group = false)
    {
        return self::find()
            ->select('_subject')
            ->where([
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
                '_semester' => $semester,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            ->groupBy(['_subject'])
            ->all();
    }

    public static function getStudentsByYearSemesterGroups($curriculum = false, $education_year = false, $semester = false, $subject = false, $groups)
    {
        return self::find()
            ->leftJoin('e_student', 'e_student.id=e_student_subject._student')
            ->leftJoin('e_student_meta', 'e_student_meta._student=e_student_subject._student')
            ->with(['student'])
            ->where([
                'e_student_subject._curriculum' => $curriculum,
                'e_student_subject._education_year' => $education_year,
                '_semester' => $semester,
                '_subject' => $subject,
                'e_student_subject.active' => self::STATUS_ENABLE,
                'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            ])
            ->andWhere(['in', 'e_student_subject._group', $groups])
            ->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC])
            ->all();
    }

    public static function getGroupsByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_curriculum', '_subject', '_student', '_group', '_education_year', '_semester'], 'required', 'on' => self::SCENARIO_INSERT],
            [['_group'], 'required', 'on' => self::SCENARIO_EDIT_GROUP],
            [['_curriculum', '_subject', '_student', '_group', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', '_student', '_group', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_education_year', '_semester'], 'string', 'max' => 64],
            [['_curriculum', '_student', '_education_year', '_semester', '_subject'], 'unique', 'targetAttribute' => ['_curriculum', '_student', '_education_year', '_semester', '_subject']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getSemestr()
    {
        return $this->hasOne(Semester::className(), ['id' => '_semester']);
    }

    public function getCurriculumSubject()
    {
        return $this->hasOne(ECurriculumSubject::className(), [
            '_curriculum' => '_curriculum',
            '_subject' => '_subject',
            '_semester' => '_semester'
        ]);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), [
            '_curriculum' => '_curriculum',
            '_education_year' => '_education_year',
            '_semestr' => '_semester',
            '_student' => '_student'
        ]);
    }

    public function getCurriculumSubjectExamType()
    {
        return $this->hasOne(ECurriculumSubjectExamType::className(), [
            '_curriculum' => '_curriculum',
            '_subject' => '_subject',
            '_semester' => '_semester'
        ]);
    }

    public function getAcademicRecord()
    {
        return $this->hasOne(EAcademicRecord::className(), [
            '_curriculum' => '_curriculum',
            '_subject' => '_subject',
            '_semester' => '_semester',
            '_student' => '_student',
        ]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_group',
                    '_student',
                    '_subject',
                    '_education_year',
                    '_semester',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            //$query->orWhereLike('name_uz', $this->search, '_translations');
            //  $query->orWhereLike('name_oz', $this->search, '_translations');
            // $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_subject._education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['e_student_subject._semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['e_student_subject._subject' => $this->_subject]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['e_student_subject._student' => $this->_student]);
        }

        return $dataProvider;
    }
}
