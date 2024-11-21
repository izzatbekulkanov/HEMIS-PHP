<?php

namespace common\models\performance;

use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculumSubject;
use common\models\system\_BaseModel;

/**
 *
 * @property int $_student_ptt
 * @property int $_subject
 * @property int $total_acload
 * @property int $grade
 * @property int $credit
 * @property int $total_point
 * @property EStudentPtt $studentPtt
 * @property ECurriculumSubject $curriculumSubject
 * @property EAcademicRecord $academicRecord
 *
 */
class EStudentPttSubject extends _BaseModel
{
    public static function tableName()
    {
        return 'e_student_ptt_subject';
    }

    public function getStudentPtt()
    {
        return $this->hasOne(EStudentPtt::className(), ['id' => '_student_ptt']);
    }

    public function getCurriculumSubject()
    {
        return $this->hasOne(ECurriculumSubject::className(), ['id' => '_curriculum_subject']);
    }


    /**
     * @return EAcademicRecord | null
     */
    public function getAcademicRecord()
    {
        return $this->hasOne(EAcademicRecord::className(), [
            '_curriculum' => '_curriculum',
            '_semester' => '_semester',
            '_subject' => '_subject',
        ])->viaTable('e_curriculum_subject', ['id' => '_curriculum_subject'])
            ->where([
                '_student' => $this->studentPtt->_student,
            ]);
    }
}
