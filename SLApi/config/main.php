<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => '45434830-715b-4912-9c33-167fd87eb301',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],

    'runtimePath' => env('APP_RUNTIME_PATH', '/data/logs/runtime'),

    'modules' => [
        'v1' => [
            'class' => 'SLApi\modules\v1\Module'
        ],

    ],

    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname'      => env('REDIS_HOST', 'localhost'),
            'port'          => env('REDIS_PORT', 6379),
            'database'      => 9,
            'password'      => env('REDIS_PASSWORD', '')
        ],
        
        'request' => [
            'csrfParam' => '_csrf-api',
            'cookieValidationKey' => 'spEUE4GN_7ZYaX3vxr5DW1FXpJ2_5-vF',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'user' => [
            'identityClass' => 'common\models\orm\XmBUser',
            'enableAutoLogin' => true,
            'enableSession' => false,
            /*'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],*/
        ],

        'session' => [
            // this is the name of the session cookie used for login on the frontend
            //'name' => 'PHPSESSID',
            'class' =>  'yii\redis\Session',
            'timeout'   =>  10,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/error.log',
                    'rotateByCopy' => false,
                    'fileMode' => 0777,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/warning.log',
                    'rotateByCopy' => false,
                    'fileMode' => 0777,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace'],
                    'logVars' => ['_FILES', '_POST', '_GET'],
                    'logFile' => '@runtime/logs/app.log',
                    'rotateByCopy' => false,
                    'fileMode' => 0777,
                ]
            ],
        ],


    ],

    'params' => $params,
];
