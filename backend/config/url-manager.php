<?php

return [
    'class' => 'codemix\localeurls\UrlManager',
    'languages' => array_flip(\common\components\Config::getShortLanguageCodes()),
    'enableLanguageDetection' => false,
    'enableLanguagePersistence' => false,
    'showScriptName' => false,
    'enablePrettyUrl' => true,
    'rules' => [
        '<controller:\w+>/<id:[a-z0-9]{24,24}>' => '<controller>/view',
        '<controller:\w+>/<action:\w+>/<id:[a-z0-9]{24,24}>' => '<controller>/<action>',
        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
        '<controller:\w+>/<action:\w+>/<type:[a-z]{3,16}>' => '<controller>/<action>',
        '<controller:\w+>/<action:\w+>/<id:[a-z0-9]{24,24}>/<social:[a-z]{3,16}>' => '<controller>/<action>',
    ],
];