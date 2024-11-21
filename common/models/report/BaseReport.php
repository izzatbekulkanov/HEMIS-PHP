<?php

namespace common\models\report;


use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;

abstract class BaseReport extends HemisApiSyncModel
{
    abstract static function runReport($syncOnlyFailed = true);

    protected $name = 'Report';

    public function getName()
    {
        return __($this->name);
    }

    public static function runAllReports($syncOnlyFailed = true)
    {
        $models = HemisApi::getApiClient()->getSyncModels();

        foreach ($models as $model) {
            if (is_subclass_of($model['class'], self::class)) {
                $model['class']::runReport($syncOnlyFailed);
            }
        }
    }
}