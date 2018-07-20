<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => '45434830-715b-4912-9c33-167fd87eb300',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],

    'runtimePath' => env('APP_RUNTIME_PATH', '/data/logs/runtime'),
    'layout' => '@Api/modules/layouts/main.php',

    'modules' => [
        'v1' => [
            'class' => 'Api\modules\v1\V1Module'
        ],
        'site' => [
            'class' => 'Api\modules\site\SiteModule'
        ]
    ],

    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
            'cookieValidationKey' => 'spEUE4GN_7ZYaX3vxr5DW1FXpJ2_5-vF',
        ],

        'user' => [
            'identityClass' => 'common\models\orm\Member',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],

        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'PHPSESSID',
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

        'errorHandler' => [
            'errorAction' => 'site/error/error',
        ],
    ],

    'params' => $params,
];
