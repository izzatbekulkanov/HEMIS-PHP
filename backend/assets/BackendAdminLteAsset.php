<?php


namespace backend\assets;


use dmstr\web\AdminLteAsset;
use yii\base\Exception;

class BackendAdminLteAsset extends AdminLteAsset
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/';
    public $css = [
        'plugins/iCheck/flat/blue.css',
        'dist/css/AdminLTE.min.css',
        'dist/css/skins/skin-blue.min.css',
    ];
    public $js = [
        'plugins/iCheck/icheck.min.js',
        'dist/js/adminlte.min.js'
    ];
    public $depends = [
        'rmrevin\yii\fontawesome\AssetBundle',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    public $skin = false;

}