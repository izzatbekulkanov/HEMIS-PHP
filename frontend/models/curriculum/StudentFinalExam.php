<?php


namespace frontend\models\curriculum;


use common\models\curriculum\EducationYear;
use common\models\curriculum\EExam;
use common\models\curriculum\EExamStudent;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class StudentFinalExam extends EExam
{

    public function searchForStudent(Student $student, Semester $semester, $params = [])
    {
        $this->load($params);

        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['educationYear', 'subject', 'employee']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['start_at' => SORT_ASC],
                'attributes' => [
                    'start_at',
                    'max_ball',
                    'duration',
                    '_subject',
                    '_employee',
                    '_semester',
                    '_language',
                    '_education_year',
                    '_exam_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_exam.name', $this->search);
        }

        $groups = implode(',', $student->getGroupIds());

        $query->andWhere(new Expression("e_exam.id in (select _exam from e_exam_group where _group IN ($groups))"));
        $query->andFilterWhere(['_education_year' => $semester->_education_year, 'e_exam.active' => true]);

        if ($this->_subject) {
            $query->andFilterWhere(['e_exam._subject' => $this->_subject]);
        }

        return $dataProvider;
    }


}