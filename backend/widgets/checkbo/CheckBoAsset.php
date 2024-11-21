<?php

namespace backend\widgets\checkbo;

use yii\web\AssetBundle;

class CheckBoAsset extends AssetBundle
{
    public $sourcePath = '@bower/checkbo';

    public $js = [
        'src/0.1.4/js/checkBo.min.js',
    ];

    public $css = [
        'src/0.1.4/css/checkBo.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}