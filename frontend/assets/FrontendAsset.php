<?php

namespace frontend\assets;

use backend\assets\BackendAsset;
use common\components\TimeStampAssetBundle;
use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class FrontendAsset extends TimeStampAssetBundle
{
    public $basePath = '@webroot';

    public $sourcePath = '@frontend/assets/frontend';

    public $css = [
        'css/front.css',
    ];

    public $js = [
        'js/front.js',
    ];

    public $depends = [
        BackendAsset::class,
    ];
}
