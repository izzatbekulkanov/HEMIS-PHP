<?php

namespace backend\assets;

use backend\components\View;
use common\components\TimeStampAssetBundle;
use yii\helpers\Json;
use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class BackendAsset extends TimeStampAssetBundle
{
    public $basePath = '@webroot';

    public $sourcePath = '@backend/assets/app';
    public $js = [
        'js/ajax-modal-popup.js',
        'js/jquery.formatter.min.js',
        'js/scripts.js',
        'js/js.cookie.js',
    ];

    public $css = [
        'css/style.css',
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\jui\JuiAsset',
        'backend\assets\VendorAsset',
        'backend\assets\BackendAdminLteAsset',
        'backend\widgets\checkbo\CheckBoAsset',
    ];

    public function registerAssetFiles($view)
    {
        $messages = [
            'deleteItem'=>__('Are you sure to delete?')
        ];
        $messages = Json::encode($messages);
        $view->registerJs("var globalMessages=$messages;", View::POS_HEAD);
        parent::registerAssetFiles($view);
    }


}
