<?php

namespace common\models\system\classifier;

class DecreeType extends _BaseClassifier
{
    public const TYPE_EXPEL = '11';
    public const TYPE_ACADEMIC_LEAVE = '12';
    public const TYPE_EXPEL_LEVEL = '13';
    public const TYPE_TRANSFER_TO_LEVEL = '14';
    public const TYPE_RESTORE = '15';
    public const TYPE_TRANSFER = '16';
    public const TYPE_GRADUATE = '17';
    public const TYPE_SCHOLARSHIP = '18';
    public const TYPE_STUDENT_PTT = '19';
    public const TYPE_GRADUATE_WORK = '20';
    public const TYPE_STUDENT_ENROLL = '21';

    public static function tableName()
    {
        return 'h_decree_type';
    }
}