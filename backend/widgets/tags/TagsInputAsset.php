<?php

namespace backend\widgets\tags;

use yii\web\AssetBundle;

class TagsInputAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery.tagsinput';

    public $js = [
        'src/jquery.tagsinput.js',
    ];

    public $css = [
        'src/jquery.tagsinput.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
}