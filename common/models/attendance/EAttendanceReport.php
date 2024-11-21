<?php

namespace common\models\attendance;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;

use common\models\system\Admin;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\TrainingType;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class EAttendanceReport
 * @property ECurriculumSubject $subject
 * @property ECurriculumSubjectDetail $subjectRealTrainingTypes
 * @package common\models\attendance
 */
class EAttendanceReport extends EStudentMeta
{
    public $_subject;
    public $_semester;
    public $absent_on;
    public $absent_off;
    public $total;
    public $percent;
    public $academic_load;
    public $student_name;

    public function getSubjectRealTrainingTypes()
    {
        return $this->hasOne(ECurriculumSubjectDetail::className(), [
            '_subject' => '_subject',
            '_curriculum' => '_curriculum',
            '_semester' => '_semester',
        ])
            ->select(['_subject', new Expression('sum(academic_load) as academic_load')])
            ->andFilterWhere(['not in', '_training_type', [TrainingType::TRAINING_TYPE_INDEPENDENT, TrainingType::TRAINING_TYPE_COURSE_WORK]])
            ->groupBy(['_subject'])
            ->with(['subject']);
    }

    /*
    public function getSubject()
    {
        return $this->hasOne(ECurriculumSubject::className(),
            [
                '_subject' => '_subject',
                '_curriculum' => '_curriculum',
                '_semester' => '_semester',
            ])
            ->with(['subject'])
            ->andFilterWhere([
                '_rating_grade' => RatingGrade::RATING_GRADE_SUBJECT,
            ])
            ->select(['_subject', new Expression('sum(total_acload) as total_acload')])
            ->groupBy(['_subject']);
    }*/

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_subject', '_semester'], 'safe']
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_subject' => __('Subject'),
                '_semester' => __('Semester'),
                'absent_off' => __('Absent Off'),
                'absent_on' => __('Absent On'),
                'total' => __('Total'),
                'percent' => __('Absent Off Percent'),
                'academic_load' => __('Real Acload'),
                'student_name' => __('Student'),
            ]
        );
    }

    public function searchForReport($params = [], Admin $admin, $faculty = null)
    {
        $this->load($params);

        if ($faculty) {
            $this->_department = $faculty;
        }

        if ($this->_education_year == null) {
            $this->_education_year = EducationYear::getCurrentYear()->code;
        }

        $query = self::find()
            ->select([
                'e_student_meta._student',
                'e_student_meta._curriculum',
                'e_student_meta._group',
                new Expression('concat(e_student.second_name,e_student.first_name) as student_name'),
                new Expression('sum(e_attendance.absent_on) as absent_on'),
                new Expression('sum(e_attendance.absent_off) as absent_off'),
                new Expression('sum(e_attendance.absent_off + e_attendance.absent_on) as total'),
            ])
            ->with([
                'student',
                'semester',
                'department',
            ])
            ->groupBy([
                'e_student_meta._student',
                'e_student_meta._curriculum',
                'e_student_meta._group',
                'student_name',
                'e_group.name',
            ]);

        $query->leftJoin('e_student', 'e_student.id = e_student_meta._student');
        $query->leftJoin('e_group', 'e_group.id = e_student_meta._group');

        $on = ['e_attendance._student = e_student_meta._student AND e_attendance._semester = e_student_meta._semestr'];
        $pars = [];
        foreach (['_semester', '_subject'] as $att) {
            if ($this->$att) {
                $on[] = "e_attendance.$att=:$att";
                $pars[$att] = $this->$att;
            }
        }
        $query->leftJoin('e_attendance', implode(' AND ', $on), $pars);

        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_meta._education_year' => $this->_education_year]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }

        if (count($admin->tutorGroups)) {
            $query->andFilterWhere(['e_student_meta._group' => array_keys($admin->tutorGroups)]);
        }

        $query->andFilterWhere(['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => self::STATUS_ENABLE]);

        return $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'e_group.name' => SORT_ASC,
                    'student_name' => SORT_ASC
                ],
                'attributes' => [
                    'e_group.name',
                    'student_name',
                ]
            ],
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);
    }


    private function getSelectQueryFilters($col)
    {
        $query = EStudentMeta::find()->select([$col])
            ->andFilterWhere([
                'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])
            ->distinct();

        foreach ([
                     '_education_type',
                     '_department',
                     '_curriculum',
                     '_group'
                 ] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    public function getCurriculumItems()
    {
        $items = ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), 'id', 'name');

        if (!isset($items[$this->_curriculum]))
            $this->_curriculum = null;

        return $items;
    }

    public function getDepartmentItems()
    {
        $items = ArrayHelper::map(
            EDepartment::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_department')])
                ->all(), 'id', 'name');

        if (!isset($items[$this->_department]))
            $this->_department = null;

        return $items;
    }

    public function getEducationTypeItems()
    {
        $items = ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_type')])
                ->all(), 'code', 'name');

        if (!isset($items[$this->_education_type]))
            $this->_education_type = null;

        return $items;
    }

    public function getGroupItems(Admin $admin)
    {
        $query = EGroup::find()
            ->orderByTranslationField('name', 'ASC')
            ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_group')]);
        if (count($admin->tutorGroups)) {
            $query->andFilterWhere(['id' => array_keys($admin->tutorGroups)]);
        }

        $items = ArrayHelper::map($query->all(), 'id', 'name');

        if (!isset($items[$this->_group]))
            $this->_group = null;

        return $items;
    }


    public function getSemesterItems()
    {
        $items = ArrayHelper::map(
            Semester::find()
                ->orderBy([
                    'position' => SORT_ASC
                ])
                ->where([
                    '_curriculum' => $this->_curriculum,
                    'active' => true,
                    'code' => ECurriculumSubject::find()
                        ->select(['_semester'])
                        ->andFilterWhere([
                            '_curriculum' => $this->_curriculum
                        ])
                        ->distinct()
                        ->column(),
                ])
                ->all(), 'code', function (Semester $item) {
            return sprintf('%s / %s / %s', $item->educationYear->name, $item->level ? $item->level->name : '--', $item->name);
        });

        if (!isset($items[$this->_semester]))
            $this->_semester = null;

        return $items;
    }

    public function getSubjectItems()
    {
        $items = ArrayHelper::map(ESubject::find()
            ->orderByTranslationField('name', 'ASC')
            ->where(['active' => true,
                'id' => ECurriculumSubject::find()
                    ->select(['_subject'])
                    ->andFilterWhere([
                        '_semester' => $this->_semester,
                        '_curriculum' => $this->_curriculum,
                        '_rating_grade' => RatingGrade::RATING_GRADE_SUBJECT,
                    ])
                    ->distinct()
                    ->column()
            ])
            ->all(), 'id', 'name');

        if (!isset($items[$this->_subject]))
            $this->_subject = null;

        return $items;
    }
}
