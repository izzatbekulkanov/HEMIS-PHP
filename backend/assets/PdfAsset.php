<?php

namespace backend\assets;

use common\components\TimeStampAssetBundle;

/**
 * Main backend application asset bundle.
 */
class PdfAsset extends TimeStampAssetBundle
{
    public $basePath = '@webroot';

    public $sourcePath = '@backend/assets/app';

    public $js = [
    ];
    public $css = [
        'css/diploma-print.css'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}
