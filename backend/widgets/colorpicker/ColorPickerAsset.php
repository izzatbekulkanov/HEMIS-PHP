<?php

namespace backend\widgets\colorpicker;


use yii\web\AssetBundle;

class ColorPickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/mjolnic-bootstrap-colorpicker/dist';

    public $js = [
        'js/bootstrap-colorpicker.min.js',
    ];

    public $css = [
        'css/bootstrap-colorpicker.min.css',
    ];

    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
}