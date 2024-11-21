<?php


namespace frontend\models\curriculum;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class StudentCurriculumSubject extends EStudentSubject
{
    public static function getSemesterSubjects(Student $student, Semester $semester)
    {
        $subjects = EStudentSubject::find()
            ->alias('c')
            ->leftJoin('e_subject s', 's.id=c._subject')
            ->where([
                '_curriculum' => $student->meta->_curriculum,
                '_student' => $student->id,
                '_semester' => $semester->code,
            ])
            ->orderBy(['s.name' => SORT_ASC])
            ->all();

        return ArrayHelper::map($subjects, '_subject', 'subject.name');
    }

    public static function getCurrentSemesterSubjectCount(Student $student, Semester $semester)
    {
        return ECurriculumSubject::find()
            ->where([
                '_curriculum' => $student->meta->_curriculum,
                '_semester' => $semester->code,
            ])
            ->count();
    }

    public function searchForStudent(Student $student, Semester $semester)
    {
        /**
         * @var $item ECurriculumSubject
         */
        $query = EStudentSubject::find()
            ->where(['_curriculum' => $student->meta->_curriculum, '_student' => $student->id, 'active' => true])
            ->orderBy(['_semester' => SORT_ASC, 'position' => SORT_ASC])
            ->with(['subject', 'semester']);

        $semesters = [];
        foreach ($query->all() as $item) {
            if (!isset($semesters[$item->_semester])) {
                $semesters[$item->_semester] = [
                    'semester' => $item->semester,
                    'subjects' => [],
                ];
            }
            $semesters[$item->_semester]['subjects'][] = $item;
        }

        return $semesters;
    }

    public function getFormattedDate()
    {
        return upperCaseFirst(\Yii::$app->formatter->asDate($this->exam_date->getTimestamp(), 'php:l, d-F, Y'));
    }


}