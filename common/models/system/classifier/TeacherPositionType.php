<?php

namespace common\models\system\classifier;

use yii\helpers\ArrayHelper;

class TeacherPositionType extends _BaseClassifier
{
    const TEACHER_POSITION_TYPE_INTERN = '11';
    const TEACHER_POSITION_TYPE_ASSISTANT = '12';
    const TEACHER_POSITION_TYPE_HEAD_TEACHER = '13';
    const TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR = '14';
    const TEACHER_POSITION_TYPE_PROFESSOR = '15';
    const TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT = '16';
    const TEACHER_POSITION_TYPE_DEAN = '25';
    const TEACHER_POSITION_TYPE_TUTOR = '34';
    const TEACHER_POSITION_TYPE_RECTOR = '20';

    const TEACHER_POSITIONS = [
        self::TEACHER_POSITION_TYPE_INTERN,
        self::TEACHER_POSITION_TYPE_ASSISTANT,
        self::TEACHER_POSITION_TYPE_HEAD_TEACHER,
        self::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR,
        self::TEACHER_POSITION_TYPE_PROFESSOR,
        self::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
    ];

    public static function tableName()
    {
        return 'h_teacher_position_type';
    }

    public static function getDirectionOptions()
    {
        return ArrayHelper::map(TeacherPositionType::find()
            ->where(['active' => true])
            ->andWhere(['not in', 'code', self::TEACHER_POSITIONS])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all(), 'code', 'name');
    }

    public static function getTeacherOptions()
    {
        return ArrayHelper::map(TeacherPositionType::find()
            ->where(['active' => true])
            ->andWhere(['in', 'code', self::TEACHER_POSITIONS])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all(), 'code', 'name');
    }
    public static function getTeacherOptionList()
    {
        return TeacherPositionType::find()
            ->where(['active' => true])
            ->andWhere(['in', 'code', self::TEACHER_POSITIONS])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();
    }
}