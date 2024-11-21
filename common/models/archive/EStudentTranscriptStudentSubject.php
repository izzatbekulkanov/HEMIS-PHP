<?php


namespace common\models\archive;


use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EStudentSubject;

/**
 * Class EStudentTranscriptStudentSubject
 * @property EAcademicRecord academicRecord
 * @package common\models\performance
 */
class EStudentTranscriptStudentSubject extends EStudentSubject
{

    /**
     * @param $studentId
     * @return EAcademicRecord | null
     */
    public function getAcademicRecordData($studentId)
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