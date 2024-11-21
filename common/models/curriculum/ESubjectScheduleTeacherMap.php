<?php

namespace common\models\curriculum;

use common\models\employee\EEmployeeMeta;
use common\models\structure\EDepartment;
use common\models\system\Admin;
use common\models\system\classifier\EmployeeType;
use common\models\system\classifier\StructureType;
use DateInterval;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 *
 */
class ESubjectScheduleTeacherMap extends ESubjectSchedule
{
    private $_weekOptions;
    public $week;
    public $_department;
    public $_cathedra;

    public function rules()
    {
        return [
            [['_cathedra', '_department', 'week'], 'safe']
        ];
    }

    public function searchForTeachers($params, Admin $admin)
    {
        $data = [];

        $this->load($params);

        if ($admin->role->isDeanOrTutorRole()) {
            if ($admin->employee->deanFaculties) {
                $this->_department = $admin->employee->deanFaculties->id;
            }
        } else if ($admin->role->isHeadOfDepartmentRole() && $admin->employee->headDepartments) {
            $this->_department = $admin->employee->headDepartments->parent;
            $this->_cathedra = $admin->employee->headDepartments->id;
        }

        $currentYear = EducationYear::getCurrentYear()->code;

        if ($this->week == null) {
            if ($options = array_keys($this->getWeekOptions())) {
                if ($value = array_shift($options)) {
                    $this->week = $value;
                }
            }
        }

        if ($this->week) {
            $pairs = [];
            foreach (LessonPair::getLessonPairByYear($currentYear) as $pair) {
                $pairs[$pair->code] = [
                    'label' => $pair->name,
                    'count' => 0,
                ];
            }

            $start = date_create_from_format('Y-m-d', $this->week);
            $days = [];

            foreach (range(0, 5) as $day) {
                $days[$start->format('Y-m-d')] = [
                    'label' => upperCaseFirst(Yii::$app->formatter->asDate($start->getTimestamp(), 'php:l, d-m-Y')),
                    'pairs' => $pairs
                ];
                $start->add(new DateInterval("P1D"));
            }

            /**
             * @var $employee EEmployeeMeta
             */
            $employees = EEmployeeMeta::find()
                ->with(['employee'])
                ->joinWith(['employee'])
                ->where([
                    'e_employee_meta.active' => true,
                    '_employee_type' => EmployeeType::EMPLOYEE_TYPE_TEACHER,
                ])
                ->orderBy(['e_employee.first_name' => SORT_ASC, 'e_employee.second_name' => SORT_ASC, 'e_employee.third_name' => SORT_ASC]);

            if ($this->_department) {
                $deps = EDepartment::find()
                    ->select(['id'])
                    ->where(['active' => true])
                    ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
                    ->andWhere(['parent' => $this->_department])
                    ->column();
                $employees->andFilterWhere(['_department' => count($deps) ? $deps : -1]);
            }

            if ($this->_cathedra) {
                $employees->andFilterWhere(['_department' => $this->_cathedra]);
            }

            foreach ($employees->all() as $employee) {
                $data[$employee->_employee] = [
                    'employee' => $employee->employee,
                    'label' => $employee->employee->getShortName(),
                    'days' => $days
                ];
            }

            $lessons = ESubjectSchedule::find()
                ->select([new Expression('lesson_date, _employee, _lesson_pair, count(1) as count')])
                ->andWhere(['>=', 'lesson_date', $this->week])
                ->andWhere(['<=', 'lesson_date', $start->format('Y-m-d')])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                ->groupBy(['lesson_date', '_employee', '_lesson_pair'])
                ->asArray()
                ->all();


            foreach ($lessons as $lesson) {
                if (@isset($data[$lesson['_employee']]['days'][$lesson['lesson_date']]['pairs'][$lesson['_lesson_pair']])) {
                    $data[$lesson['_employee']]['days'][$lesson['lesson_date']]['pairs'][$lesson['_lesson_pair']]['count'] = $lesson['count'];
                }
            }

            return [
                'employees' => $data,
                'pairs' => $pairs,
                'days' => $days
            ];
        }

        return [];
    }

    public function getWeekOptions()
    {
        if ($this->_weekOptions == null) {
            $currentYear = EducationYear::getCurrentYear()->code;
            $weeks = ESubjectSchedule::find()
                ->orderBy(['week' => SORT_ASC])
                ->where(['_education_year' => $currentYear])
                ->select([new Expression("date_trunc('week', lesson_date::date)::date AS week")])
                ->groupBy(['week'])
                ->asArray()
                ->all();

            $this->_weekOptions = ArrayHelper::map($weeks, 'week', function ($item) {
                $date = date_create_from_format('Y-m-d', $item['week']);

                return sprintf("%s - %s",
                    Yii::$app->formatter->asDate($date->getTimestamp(), 'php:d F'),
                    Yii::$app->formatter->asDate($date->add(new DateInterval('P5D'))->getTimestamp(), 'php:d F, Y')
                );
            });
        }

        return $this->_weekOptions;
    }

    public function getFacultyOptions()
    {
        return ArrayHelper::map(EDepartment::find()
            ->where(['active' => true])
            ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_FACULTY]])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public function getCathedraOptions()
    {
        $query = EDepartment::find()
            ->where(['active' => true])
            ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
            ->orderByTranslationField('name');

        if ($this->_department) {
            $query->andFilterWhere(['parent' => $this->_department]);
        }

        return ArrayHelper::map($query->all(), 'id', 'name');
    }
}
