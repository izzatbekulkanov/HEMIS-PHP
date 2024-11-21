<?php


namespace frontend\models\curriculum;


use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\performance\EPerformance;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

class StudentPerformance extends EPerformance
{
    public function getTimelineForStudent(Student $student, $params)
    {
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['group', 'semester', 'group', 'subject', 'examType', 'lessonPair', 'auditorium', 'employee']);


        $query->andFilterWhere([
            '_education_year' => EducationYear::getCurrentYear()->code,
            '_group' => $student->getGroupIds(),
        ]);


    }

    public $_results = [];

    public function searchForStudent(Student $student, $params)
    {
        /**
         * @var $item self
         */
        $this->load($params);

        if ($this->_semester == null) {
           // $this->_semester = $student->meta->curriculum->lastSemester->code;
        }

        $query = self::find()
            ->alias('p')
            ->leftJoin('e_subject', 'e_subject.id=p._subject')
            ->leftJoin('e_employee', 'e_employee.id=p._employee')
            ->leftJoin('h_exam_type et', 'et.code=p._exam_type')
            ->with(['semester', 'subject', 'examType', 'employee'])
            ->orderBy(['p._semester' => SORT_ASC, 'p._subject' => SORT_ASC, 'et.position' => SORT_ASC]);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
        }

        $query->andFilterWhere([
            'p._student' => $student->id,
        ]);


        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($this->_semester) {
            $query->andFilterWhere(['p._semester' => $this->_semester]);
        }

        $result = [];
        $types = [];

        foreach ($query->all() as $item) {
            if (!isset($types[$item->_exam_type])) {
                $types[$item->_exam_type] = $item->examType;
            }
            if (!isset($result[$item->_semester])) {
                $result[$item->_semester] = [
                    'semester' => $item->semester,
                    'subjects' => [],
                ];
            }
            if (!isset($result[$item->_semester]['subjects'][$item->_subject])) {
                $result[$item->_semester]['subjects'][$item->_subject] = $item;
            }

            $result[$item->_semester]['subjects'][$item->_subject]->_results[$item->_exam_type][$item->_final_exam_type] = $item;
        }

        return [
            'types' => $types,
            'items' => $result,
        ];
    }

    public function getFormattedDate()
    {
        return upperCaseFirst(\Yii::$app->formatter->asDate($this->exam_date->getTimestamp(), 'php:l, d - F, Y'));
    }


}