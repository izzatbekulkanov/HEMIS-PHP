<?php


namespace frontend\models\curriculum;

use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class CurriculumSubject extends ECurriculumSubject
{
    public static function getSubjectData(Student $student, Semester $semester, $subject=false)
    {
        return self::find()
            ->where([
                '_curriculum' => $student->meta->_curriculum,
                '_semester' => $semester->code,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE,
            ])
            ->one();
    }

}