<?php
/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
return [
    'Development' => [
        'path' => 'dev',
        'setWritable' => [
            'console/runtime',
            'backend/runtime',
            'frontend/runtime',
            'backend/web/assets',
            'frontend/web/assets',
            'static',
            'static/uploads',
            'static/assets',
            'private'
        ],
        'setExecutable' => [
            'yii',
            'tests/codeception/bin/yii',
        ],
        'setCookieValidationKey' => [
            'backend/config/main-local.php',
            'frontend/config/main-local.php',
        ],
        'setEncryptionKey' => [
            'common/config/params-local.php',
        ],
        'createSymlink' => [
            'backend/web/static'=>'static',
        ],
    ],
    'Production' => [
        'path' => 'prod',
        'setWritable' => [
            'console/runtime',
            'backend/runtime',
            'backend/web/assets',
            'frontend/web/assets',
            'frontend/runtime',
            'static',
            'static/uploads',
            'static/assets',
            'private',
        ],
        'setExecutable' => [
            'yii',
        ],
        'setCookieValidationKey' => [
            'backend/config/main-local.php',
            'frontend/config/main-local.php',
        ],
        'setEncryptionKey' => [
            'common/config/params-local.php',
        ],
        'createSymlink' => [
            'backend/web/static'=>'static',
        ],
    ],
];

