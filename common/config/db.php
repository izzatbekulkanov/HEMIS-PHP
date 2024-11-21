<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('DB_DSN'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    'enableQueryCache' => false,
    'schemaCacheDuration' => 300,
    'queryCacheDuration' => 3600,
    'on afterOpen' => function ($event) {
        $event->sender->createCommand("SET TIME ZONE 'UTC';")->execute();
    },
    'schemaMap' => [
        'pgsql' => 'common\components\db\Schema',
    ],
];
