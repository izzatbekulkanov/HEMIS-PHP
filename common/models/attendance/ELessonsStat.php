<?php

namespace common\models\attendance;


use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectSchedule;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\system\Admin;
use common\models\system\classifier\StructureType;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class ELessonsStat
 * @package common\models\attendance
 */
class ELessonsStat extends ESubjectSchedule
{

    public $_department;
    public $start_date;
    public $end_date;
    public $group_by;
    public $count;

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getGroupByOptions()
    {
        return [
            'teacher' => __('By Teacher'),
            'group' => __('By Group'),
            'department' => __('By Faculty'),
            '_lesson_pair' => __('By Lesson Pair'),
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'count' => __('Count'),
            'group_by' => __('Guruhlash'),
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_department', 'search', '_education_year', 'end_date', 'start_date', 'group_by'], 'safe']
        ]);
    }


    public function searchAttendance($params, Admin $admin, $faculty = null)
    {
        $this->load($params);

        if ($faculty) {
            $this->_department = $faculty;
        }

        $query = self::find()
            ->joinWith(['employee', 'group', 'lessonPair', 'attendanceControl', 'subject', 'trainingType', 'week']);


        if ($this->search) {
            if (is_numeric($this->search) && intval($this->search) < 10) {
                $query->andFilterWhere(['h_lesson_pair.name' => $this->search]);
            } else {
                $query->orWhereLike('e_employee.employee_id_number', $this->search);
                $query->orWhereLike('e_employee.second_name', $this->search);
                $query->orWhereLike('e_employee.first_name', $this->search);
                $query->orWhereLike('e_employee.third_name', $this->search);
                $query->orWhereLike('e_group.name', $this->search);
                $query->orWhereLikeTranslation('name', $this->search, 'e_subject._translations');
            }
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_group._department' => $this->_department]);
        }

        if (count($admin->tutorGroups)) {
            $query->andFilterWhere(['e_subject_schedule._group' => array_keys($admin->tutorGroups)]);
        }


        $educationYear = EducationYear::getCurrentYear();
        $dateSelected = false;
        if ($this->start_date) {
            if ($date = date_create_from_format('Y-m-d', $this->start_date)) {
                $query->andFilterWhere(['>=', 'e_subject_schedule.lesson_date', $date->format('Y-m-d H:i')]);
                $dateSelected = true;
            }
        }

        if ($this->end_date) {
            if ($date = date_create_from_format('Y-m-d', $this->end_date)) {
                $query->andFilterWhere(['<=', 'e_subject_schedule.lesson_date', $date->format('Y-m-d H:i')]);
                $dateSelected = true;
            }
        }

        $query->andFilterWhere(['e_subject_schedule._education_year' => $educationYear->code]);

        $query->andWhere(new Expression('e_attendance_control._subject is NULL'));

        if ($this->group_by == 'teacher') {
            $query->select(['e_subject_schedule._employee', 'count(1) as count'])
                ->groupBy(['e_subject_schedule._employee']);
        }

        if ($this->group_by == 'group') {
            $query->select(['e_subject_schedule._group', 'count(1) as count'])
                ->groupBy(['e_subject_schedule._group']);
        }

        if ($this->group_by == 'department') {
            $query->select(['e_group._department as _department', 'count(1) as count'])
                ->groupBy(['e_group._department']);
        }

        if ($this->group_by == 'lesson_date') {
            $query->select(['e_subject_schedule.lesson_date', 'count(1) as count'])
                ->groupBy(['e_subject_schedule.lesson_date'])
                ->orderBy(['lesson_date' => SORT_DESC]);
        }

        if ($this->group_by == '_lesson_pair') {
            $query->select(['e_subject_schedule._lesson_pair', 'count(1) as count', 'e_subject_schedule._education_year'])
                ->groupBy(['e_subject_schedule._lesson_pair', 'e_subject_schedule._education_year']);
        }

        return $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $this->group_by == null ? ['lesson_date' => SORT_DESC, '_lesson_pair' => SORT_ASC] : ['count' => SORT_DESC],
                'attributes' => [
                    '_lesson_pair',
                    'count',
                    'lesson_date'
                ]
            ],
            'pagination' => [
                'pageSize' => $this->group_by == null ? 100 : 100000,
            ],
        ]);
    }


    public function getDepartmentItems()
    {
        $items = ArrayHelper::map(
            EDepartment::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'id' => EGroup::find()
                        ->select(['_department'])
                        ->andWhere([
                            'id' => self::find()
                                ->select(['_group'])
                                ->distinct()
                                ->column()
                        ])
                        ->distinct()
                        ->column()
                ])
                ->all(), 'id', 'name');

        return $items;
    }


    public function getEducationYearItems()
    {
        $items = ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['code' => SORT_DESC])
                ->where(['code' => self::find()->select(['_education_year'])->distinct()->column()])
                ->all(), 'code', 'name');

        return $items;
    }

}