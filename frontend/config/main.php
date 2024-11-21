<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-student',
    'name' => 'HEMIS Student',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'dashboard/index',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => frontend\models\system\Student::class,
            'enableAutoLogin' => true,
            'autoRenewCookie' => false,
            'loginUrl' => '/dashboard/login',
            'identityCookie' => [
                'name' => '_frontendUser',
                'httpOnly' => true,
            ],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'frontend',
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
                ],
            ],
        ],
        'view' => [
            'class' => 'frontend\components\View',
        ],
        'errorHandler' => [
            'errorAction' => 'dashboard/error',
        ],
        'assetManager' => [
            'linkAssets' => false,
            'appendTimestamp' => true,
            'basePath' => '@frontend/web/assets',
            'baseUrl' => '/assets/',
        ],
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',
            'languages' => array_flip(\common\components\Config::getShortLanguageCodes()),
            'enableLanguageDetection' => false,
            'enableLanguagePersistence' => false,
            'showScriptName' => false,
            'enablePrettyUrl' => true,
            'ignoreLanguageUrlPatterns' => [
                '#^api/#' => '#^api/#'
            ],
            'rules' => [
                'api/<controller:\w+>/<action:\w+>/<id:\d+>' => 'api/<controller>/<action>',
                'api/<controller:\w+>/<action:\w+>' => 'api/<controller>/<action>',
                '<controller:\w+>/<id:[a-z0-9]{24,24}>' => '<controller>/view',
                '<controller:\w+>/<id:[a-z0-9]{24,24}>/<semester:[0-9]{1,6}>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:[0-9]{1,}>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<semester:[0-9]{1,6}>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<type:[a-z]{3,16}>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:[a-z0-9]{24,24}>/<semester:[0-9]{1,6}>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
