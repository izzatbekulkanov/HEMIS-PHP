<?php

namespace common\models\system\classifier;

class AcademicRank extends _BaseClassifier
{
    const ACADEMIC_RANK_NONE = '10';
    const ACADEMIC_RANK_DOCENT = '11';
    const ACADEMIC_RANK_SCIENTIFIC = '12';
    const ACADEMIC_RANK_PROFESSOR = '13';

    public static function tableName()
    {
        return 'h_academic_rank';
    }
}