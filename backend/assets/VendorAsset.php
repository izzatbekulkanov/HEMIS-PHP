<?php

namespace backend\assets;

use common\components\TimeStampAssetBundle;
use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class VendorAsset extends TimeStampAssetBundle
{
    public $basePath = '@webroot';

    public $sourcePath = '@vendor/bower-asset';

    public $js  = [
        'theia-sticky-sidebar/dist/theia-sticky-sidebar.min.js',
    ];
    public $css = [
        'chosen_v1.4.0/chosen.min.css',
        'components-font-awesome/css/font-awesome.min.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}
