<?php

namespace common\models\curriculum;

use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\classifier\Language;
use common\models\system\classifier\StudentStatus;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 *
 * @property int $id
 * @property int $_exam
 * @property int $_student
 * @property EExam $exam
 * @property EStudent $student
 */
class EExamExclude extends ActiveRecord
{
    public static function tableName()
    {
        return 'e_exam_exclude';
    }

    public function getExam()
    {
        return $this->hasOne(EExam::className(), ['id' => '_exam']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }
}
