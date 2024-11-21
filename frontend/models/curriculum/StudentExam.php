<?php


namespace frontend\models\curriculum;


use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentExam extends ESubjectExamSchedule
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

    public function searchForStudent(Student $student, Semester $semester)
    {
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['group', 'semester', 'group', 'subject', 'examType', 'lessonPair', 'auditorium', 'employee']);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['exam_date' => SORT_ASC, '_lesson_pair' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_education_year',
                    '_semester',
                    'exam_date',
                    '_lesson_pair',
                    '_group',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        $query->andFilterWhere([
            '_education_year' => EducationYear::getCurrentYear()->code,
            '_group' => $student->getGroupIds(),
        ]);

        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }

        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($semester) {
            $query->andFilterWhere(['_semester' => $semester->code]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }

        return $dataProvider;
    }


    public function getFormattedDate()
    {
        return upperCaseFirst(\Yii::$app->formatter->asDate($this->exam_date->getTimestamp(), 'php:l, d-F, Y'));
    }


}