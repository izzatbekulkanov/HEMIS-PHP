<?php

namespace backend\widgets\filekit\widget;

use yii\web\AssetBundle;

class BlueimpLoadImageAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/blueimp-load-image';

    public $js = [
        'js/load-image.all.min.js'
    ];
}
