<?php


namespace frontend\models\curriculum;


use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\Semester;
use common\models\system\classifier\TrainingType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class SubjectTask extends ESubjectTaskStudent
{
    public static function getTaskBySubjectTrainingStudent($curriculum = false, $semester = false, $subject = false, $tasks = false, Student $student, $task_type = false)
    {
        if ($tasks) {
            return self::find()
                ->where([
                    '_curriculum' => $curriculum,
                    '_semester' => $semester,
                    '_subject' => $subject,
                    '_student' => $student->id,
                    'final_active' => 1,
                    'active' => self::STATUS_ENABLE,
                ])
                ->andWhere(new Expression('_subject_task is not null'))
                ->all();
        } else {
            return self::find()
                ->where([
                    '_curriculum' => $curriculum,
                    '_semester' => $semester,
                    '_subject' => $subject,
                    '_student' => $student->id,
                    'final_active' => 1,
                    'active' => self::STATUS_ENABLE
                ])
                ->andWhere(new Expression('_subject_resource is not null'))
                ->all();
        }
    }

    public function searchForStudent(Student $student, Semester $semester, ECurriculumSubject $subject = null, $training_type)
    {
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task')
            ->with(['semester', 'subject', 'employee', 'subjectTask', 'trainingType', 'taskStudentActivity']);
        $query->modelClass = SubjectTaskStudent::className();

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['e_subject_task.deadline' => SORT_ASC, 'position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_employee',
                    '_semester',
                    '_language',
                    '_education_year',
                    '_training_type',
                    'e_subject_task_student._task_type',
                    'e_subject_task.deadline',
                    'position',
                    'final_active',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        $query->andFilterWhere([
            // '_education_year' => EducationYear::getCurrentYear()->code,
            //'_group' => $student->getGroupIds(),
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($training_type) {
            $query->andFilterWhere(['e_subject_task_student._training_type' => $training_type]);
        }

        $query->andFilterWhere(['e_subject_task_student.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['e_subject_task_student._curriculum' => $student->meta->_curriculum]);
        $query->andFilterWhere(['e_subject_task_student._semester' => $semester->code]);

        if ($subject)
            $query->andFilterWhere(['e_subject_task_student._subject' => $subject->_subject]);
        //$query->andFilterWhere(['e_subject_task_student._task_type' => $task_type]);
        $query->andFilterWhere(['_student' => $student->id]);
        $query->andFilterWhere(['final_active' => 1]);
        $query->andWhere(new Expression('_subject_task is not null'));

        // $query->andFilterWhere(['_group' => $student->meta->_group]);
        // $query->andFilterWhere(['e_subject_task._language' => $student->meta->group->_education_lang]);
        // $query->andFilterWhere(['e_subject_task_student._employee' => $teachers]);
        return $dataProvider;
    }
}