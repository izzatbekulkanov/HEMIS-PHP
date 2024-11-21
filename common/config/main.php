<?php

use common\components\Config;

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$config = [
    'id' => 'univer',
    'version' => APP_VERSION,
    'basePath' => dirname(__DIR__),
    'timeZone' => 'UTC',
    'language' => Config::LANGUAGE_DEFAULT,
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => ['log', 'config'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],

    'components' => [
        'config' => [
            'class' => 'common\components\Config',
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'common\components\DbMessageSource',
                    'forceTranslation' => true,
                    'enableCaching' => true,
                    'cachingDuration' => 3600,
                    'sourceLanguage' => Config::LANGUAGE_ENGLISH,
                    'sourceMessageTable' => 'e_system_message',
                    'messageTable' => 'e_system_message_translation',
                    'on missingTranslation' => [
                        'common\components\EventHandlers',
                        'handleMissingTranslation',
                    ],
                ],
                'yii' => [
                    'class' => 'common\components\DbMessageSource',
                    'forceTranslation' => true,
                    'enableCaching' => true,
                    'cachingDuration' => 3600,
                    'sourceLanguage' => Config::LANGUAGE_ENGLISH,
                    'sourceMessageTable' => 'e_system_message',
                    'messageTable' => 'e_system_message_translation',
                    'on missingTranslation' => [
                        'common\components\EventHandlers',
                        'handleMissingTranslation',
                    ],
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('SMTP_HOST'),
                'username' => getenv('EMAIL_LOGIN'),
                'password' => getenv('EMAIL_PASSWORD'),
                'port' => getenv('SMTP_PORT'),
                'encryption' => 'tls',
            ],
            'useFileTransport' => false,
        ],
        'hemisApi' => require(__DIR__ . '/hemis.php'),
        'db' => require(__DIR__ . '/db.php'),
        'assetManager' => [
            'linkAssets' => false,
            'appendTimestamp' => true,
            'basePath' => '@static/assets',
            'baseUrl' => getenv('STATIC_URL') . 'assets/',
            'bundles' => [
                'yii2fullcalendar\CoreAsset' => [
                    'js' => [
                        'fullcalendar.js',
                        'lang-all.js',
                    ],
                ]
            ]
        ],
        /*'assetsAutoCompress' => [
            'class'           => '\skeeks\yii2\assetsAuto\AssetsAutoCompressComponent',
            'enabled'         => false,
            'cssFileCompile'  => false,
            'cssFileCompress' => false,
            'jsCompress'      => false,
            'jsFileCompile'   => false,
            'jsFileCompress'  => false,
        ],*/
        'formatter' => [
            'class' => 'common\components\Formatter',
            'currencyCode' => 'UZS',
            'dateFormat' => 'dd.MM.yyyy',
            'datetimeFormat' => 'dd.MM.yyyy HH:mm:ss',
            'decimalSeparator' => '.',
            'thousandSeparator' => ' ',
            'timeZone' => 'Asia/Tashkent',
            'defaultTimeZone' => 'Asia/Tashkent',
        ],
        'reCaptcha' => [
            'class' => 'himiklab\yii2\recaptcha\ReCaptchaConfig',
            'siteKeyV3' => getenv('RECAPTCHA_KEY'),
            'secretV3' => getenv('RECAPTCHA_SECRET'),
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOST') ?: 'localhost',
            'port' => 6379,
            'database' => YII_DEBUG ? 0 : 1,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'fileStorage' => [
            'class' => '\trntv\filekit\Storage',
            'baseUrl' => '@staticUrl/uploads',
            'maxDirFiles' => 4096,
            'filesystem' => [
                'class' => 'common\components\file\LocalFileSystemBuilder',
                'path' => '@static/uploads',
            ],
        ],
        /*'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],*/
        'queueFile' => [
            'class' => \yii\queue\redis\Queue::class,
            'attempts' => 2,
            'channel' => 'file-generate'
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'attempts' => 3,
            'channel' => md5(__DIR__)
        ],
    ],
    'params' => $params,
];
return $config;
