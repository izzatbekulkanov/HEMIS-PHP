<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'name' => 'HEMIS OTM',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute' => 'dashboard/index',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'csrfCookie' => [
                'httpOnly' => true,
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\system\Admin',
            'enableAutoLogin' => true,
            'autoRenewCookie' => false,
            'loginUrl' => '/dashboard/login',
            'identityCookie' => [
                'name' => '_backendUser_' . APP_VERSION_MINOR,
                'httpOnly' => true,
            ],
        ],
        'view' => [
            'class' => 'backend\components\View',
        ],
        'session' => [
            'name' => 'backend_' . APP_VERSION_MINOR,
            'cookieParams'=>[
                //'secure' => true
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['$_GET', '$_POST'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'dashboard/error',
        ],
        'urlManager' => require(__DIR__ . '/url-manager.php'),
        'assetManager' => array(
            'linkAssets' => false,
            'appendTimestamp' => true,
            'bundles' => [
            ],
        ),
    ],
    'params' => $params,
];
