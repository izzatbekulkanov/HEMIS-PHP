<?php


namespace common\models\archive;


use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;

/**
 * Class EStudentPttCurriculumSubject
 * @property EAcademicRecord academicRecord
 * @package common\models\performance
 */
class EStudentTranscriptCurriculumSubject extends ECurriculumSubject
{
    /*public $_student;
    public $_education_year;
    public $curriculum_name;
    public $education_year_name;
    public $semester_name;
    public $subject_name;
    public $total_point;
    public $grade;*/

    /**
     * @param $studentId
     * @return EAcademicRecord | null
     */
    public function getAcademicRecord($studentId)
    {
        return $this->hasOne(EAcademicRecord::className(), [
            '_curriculum' => '_curriculum',
            '_semester' => '_semester',
            '_subject' => '_subject',
        ])->where([
            '_student' => $studentId,
        ])->andFilterWhere(['>', 'grade', 0])->one();
    }

}