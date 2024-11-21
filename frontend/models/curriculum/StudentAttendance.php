<?php


namespace frontend\models\curriculum;


use common\models\attendance\EAttendance;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

class StudentAttendance extends EAttendance
{

    public function searchForStudentWeekly(Student $student, $params)
    {
        /**
         * @var $item self
         */
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['semester', 'subject', 'lessonPair', 'employee', 'trainingType'])
            ->orderBy(['_semester' => SORT_ASC, '_subject' => SORT_ASC, 'lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC]);

        $result = [];

        foreach ($query->all() as $item) {
            if (!isset($result[$item->lesson_date->getTimestamp()])) {
                $result[$item->lesson_date->getTimestamp()] = [
                    'date' => $item->lesson_date,
                    'items' => [],
                ];
            }
            $result[$item->lesson_date->getTimestamp()]['items'][] = $item;
        }

        return new ArrayDataProvider([
            'allModels' => array_values($result),
            'pagination' => [
                'pageSize' => 6,
            ],
        ]);
    }

    public function searchForStudent(Student $student, Semester $semester, $params)
    {
        $this->load($params);

        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['semester', 'subject', 'employee', 'trainingType'])
            ->orderBy(['_semester' => SORT_DESC, 'lesson_date' => SORT_DESC, '_lesson_pair' => SORT_DESC]);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['_semester' => SORT_DESC, 'lesson_date' => SORT_DESC, '_lesson_pair' => SORT_DESC],
                'attributes' => [
                    '_subject',
                    '_education_year',
                    '_training_type',
                    '_semester',
                    'lesson_date',
                    '_lesson_pair',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        $query->andFilterWhere([
            '_student' => $student->id,
        ]);

        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($semester->code) {
            $query->andFilterWhere(['_semester' => $semester->code]);
        }

        return $dataProvider;
    }

    public function getFormattedDate()
    {
        return \Yii::$app->formatter->asDate($this->lesson_date->getTimestamp(), 'php:d-m-Y ') . $this->lessonPair->start_time;
    }

}