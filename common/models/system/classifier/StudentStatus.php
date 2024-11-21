<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class StudentStatus extends _BaseClassifier
{
    const STUDENT_TYPE_APPLIED = '10';
    const STUDENT_TYPE_STUDIED = '11';
    const STUDENT_TYPE_EXPEL = '12';
    const STUDENT_TYPE_ACADEMIC = '13';
    const STUDENT_TYPE_GRADUATED = '14';
    const STUDENT_TYPE_GRADUATED_SIMPLE = '141';
    const STUDENT_TYPE_COURSE_EXPEL = '15';

    public static function tableName()
    {
        return 'h_student_status';
    }

    public static function getTransferStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [StudentStatus::STUDENT_TYPE_EXPEL, StudentStatus::STUDENT_TYPE_GRADUATED, StudentStatus::STUDENT_TYPE_ACADEMIC, StudentStatus::STUDENT_TYPE_COURSE_EXPEL]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getRestoreStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [StudentStatus::STUDENT_TYPE_EXPEL, StudentStatus::STUDENT_TYPE_ACADEMIC, StudentStatus::STUDENT_TYPE_COURSE_EXPEL]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getReturnStatusOptions()
    {
        $items = self::find()
            ->where(['active' => true, 'code' => [StudentStatus::STUDENT_TYPE_STUDIED]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public function isStudyingStatus()
    {
        return $this->code === self::STUDENT_TYPE_STUDIED;
    }

    public function isExpelStatus()
    {
        return ($this->code === self::STUDENT_TYPE_EXPEL || $this->code === self::STUDENT_TYPE_ACADEMIC || $this->code === self::STUDENT_TYPE_GRADUATED);
    }

}