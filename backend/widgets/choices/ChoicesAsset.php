<?php

namespace backend\widgets\choices;


use yii\web\AssetBundle;

class ChoicesAsset extends AssetBundle
{
    public $sourcePath = '@backend/widgets/choices/assets';

    public $js = [
        'choices.min.js',
    ];

    public $css = [
       // 'base.min.css',
        'choices.min.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}