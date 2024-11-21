<?php

namespace frontend\models\archive;

use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class AcademicRecord extends EAcademicRecord
{
    public static function getSemesterSubjects(Student $student, Semester $semester)
    {
        $subjects = ECurriculumSubject::find()
            ->alias('c')
            ->leftJoin('e_subject s', 's.id=c._subject')
            ->where([
                '_curriculum' => $student->meta->_curriculum,
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
         * @var $item EAcademicRecord
         */
        $query = EAcademicRecord::find()
            ->where(['_student' => $student->id, 'active' => true])
            ->orderBy(['_semester' => SORT_ASC, 'position' => SORT_ASC])
            ->with(['student', 'semester']);

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