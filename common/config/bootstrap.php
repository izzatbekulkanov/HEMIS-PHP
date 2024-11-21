<?php

use yii\helpers\Url;

use yii\bootstrap\Html;
use yii\helpers\StringHelper;

define('APP_VERSION_MINOR', '8');
define('APP_VERSION', '0.8.7.10');
define('DS', DIRECTORY_SEPARATOR);
define('HTTPS_ON', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
define('HEMIS_INTEGRATION', getenv('HEMIS_INTEGRATION') === 'true');


Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('root', dirname(dirname(__DIR__)) . DS);
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@static', dirname(dirname(__DIR__)) . '/static');
Yii::setAlias('@uploads', dirname(dirname(__DIR__)) . '/uploads');
Yii::setAlias('@backups', dirname(dirname(__DIR__)) . '/backups');
Yii::setAlias('@private', dirname(dirname(__DIR__)) . '/private');
Yii::setAlias('@frontendUrl', getenv('FRONTEND_URL'));
Yii::setAlias('@backendUrl', getenv('BACKEND_URL'));
Yii::setAlias('@staticUrl', getenv('STATIC_URL'));
Yii::setAlias('@apiUrl', getenv('API_URL'));

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    $_SERVER['HTTPS'] = 'on';

if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = trim($ips[0]);
} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
}

function linkTo($params, $schema = false)
{
    return Url::to($params, $schema);
}

function currentTo($params, $schema = false)
{
    return Url::current($params, $schema);
}

function upperCaseFirst($text)
{
    return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

function __($message, $params = [], $language = false)
{
    $tags = [
        'br' => '<br>',
        'b' => '<b>',
        '/b' => '</b>',
        'span' => '<span>',
        '/span' => '</span>',
    ];
    return Yii::t('app', trim($message), array_merge($tags, $params), $language ?: Yii::$app->language);
}


function gen_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function formatDate(DateTime $dateTime)
{
    return Yii::$app->formatter->asDate($dateTime);
}

require(__DIR__ . '/events.php');