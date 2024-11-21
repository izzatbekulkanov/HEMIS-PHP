<?php

namespace common\models\system\classifier;

class EducationWeekType extends _BaseClassifier
{
    const EDUCATION_WEEK_TYPE_THEORETICAL = '11'; //Nazariy ta’lim
    const EDUCATION_WEEK_TYPE_ATTESTATION = '12'; //Attestatsiya
    const EDUCATION_WEEK_TYPE_PRACTICUM = '13'; //Malakaviy amaliyot
    const EDUCATION_WEEK_TYPE_GOV_ATTESTATION = '14'; //Davlat attestatsiyasi
    const EDUCATION_WEEK_TYPE_GOV_GRADUATION = '15'; //Bitiruv ishi ximoyasi
    const EDUCATION_WEEK_TYPE_HOLIDAY = '16'; //Ta’til
    const EDUCATION_WEEK_TYPE_CREDIT = '17'; //Kredit tizimiga kirish
    const EDUCATION_WEEK_TYPE_SCIENCE = '18'; //Ilmiy amaliyot

    public static function tableName()
    {
        return 'h_education_week_type';
    }
}