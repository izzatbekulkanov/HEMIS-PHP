<?php


namespace frontend\models\curriculum;


use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\Semester;
use common\models\system\classifier\TrainingType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class SubjectTaskActivity extends EStudentTaskActivity
{

    public static function getMarkByCurriculumSubjectTrainStudent($curriculum = false, $semester = false, $subject = false, $training_type = false, Student $student, $task_type = false)
    {
        if($training_type == TrainingType::TRAINING_TYPE_LECTURE) {
            return self::find()
                ->where([
                    '_curriculum' => $curriculum,
                    '_semester' => $semester,
                    '_subject' => $subject,
                    '_training_type' => $training_type,
                    '_student' => $student->id,
                    '_task_type' =>$task_type,
                    'active' => self::STATUS_ENABLE
                ])
                ->count();
        }
        else{
            return self::find()
                ->where([
                    '_curriculum' => $curriculum,
                    '_semester' => $semester,
                    '_subject' => $subject,
                    '_student' => $student->id,
                    '_task_type' =>$task_type,
                    'active' => self::STATUS_ENABLE
                ])
                ->andWhere(['not', ['_training_type' => TrainingType::TRAINING_TYPE_LECTURE]])
                ->count();
        }
    }



    public function searchForStudentData(Student $student, Semester $semester, SubjectTaskStudent $task)
    {
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task')
            ->with(['semester', 'subject', 'employee', 'subjectTask', 'trainingType']);

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
                    'e_subject_task.deadline',
                    'filename',
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
           // '_education_year' => EducationYear::getCurrentYear()->code,
            //'_group' => $student->getGroupIds(),
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        //$query->andFilterWhere(['e_student_task_activity.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['e_student_task_activity._curriculum' => $student->meta->_curriculum]);
        $query->andFilterWhere(['e_student_task_activity._semester' => $semester->code]);
        $query->andFilterWhere(['e_student_task_activity._subject' => $task->_subject]);
        $query->andFilterWhere(['e_student_task_activity._subject_task' => $task->_subject_task]);
        $query->andFilterWhere(['_student' => $student->id]);
        $query->andFilterWhere(['e_subject_task._language' => $student->meta->group->_education_lang]);
        return $dataProvider;
    }

}