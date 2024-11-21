<?php


namespace common\models\performance;


use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;

/**
 * Class EStudentPttCurriculumSubject
 * @property EAcademicRecord academicRecord
 * @package common\models\performance
 */
class EStudentPttCurriculumSubject extends ECurriculumSubject
{
    public $_student;

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