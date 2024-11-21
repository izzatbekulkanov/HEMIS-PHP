<?php


namespace common\components\hemis\sync;


use common\components\hemis\HemisApiSyncModel;
use common\models\structure\EUniversity;

class BaseApiUpdater extends \common\components\hemis\HemisApi
{

    public static function getModelDate($date)
    {
        return $date instanceof \DateTime ? $date->format('Y-m-d') : $date;
    }

    public static function getModelYear($date)
    {
        return $date instanceof \DateTime ? $date->format('Y') : substr($date, 0, 4);
    }

    public static function getUniversity()
    {
        return EUniversity::findCurrentUniversity()->code;
    }
}