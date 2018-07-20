<?php

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',

    'timeZone'       => env('APP_TIMEZONE', 'PRC'),
    'language'       => env('APP_LANGUAGE', 'zh-CN'),
    'sourceLanguage' => env('APP_SOURCE_LANGUAGE', 'zh-CN'),

    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
            ],
        ],

        'Aliyunoss' => [
            'class' => 'common\widgets\Aliyunoss',
        ],

        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => env2dsn('DB_HOST', 'DB_PORT', 'DB_DATABASE'),
            'username' => env('DB_USERNAME', 'forget'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),

            // 配置从服务器
            'slaveConfig' => [
                'username' => env('DB_USERNAME1', 'forget'),
                'password' => env('DB_PASSWORD1', ''),
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => env('DB_CHARSET', 'utf8'),
            ],

            // 配置从服务器组
            'slaves' => [
                ['dsn' => env2dsn('DB_HOST1', 'DB_PORT1', 'DB_DATABASE1')],
            ],
        ],

        'db2' => [
            'class' => 'yii\db\Connection',
            'dsn' => env2dsn('DB_HOST2', 'DB_PORT2', 'DB_DATABASE2'),
            'username' => env('DB_USERNAME2', 'forget'),
            'password' => env('DB_PASSWORD2', ''),
            'charset' => env('DB_CHARSET2', 'utf8'),

            // 配置从服务器
            'slaveConfig' => [
                'username' => env('DB_USERNAME3', 'forget'),
                'password' => env('DB_PASSWORD3', ''),
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => env('DB_CHARSET', 'utf8'),
            ],

            // 配置从服务器组
            'slaves' => [
                ['dsn' => env2dsn('DB_HOST3', 'DB_PORT3', 'DB_DATABASE3')],
            ],
        ],

        'db4' => [
            'class' => 'yii\db\Connection',
            'dsn' => env2dsn('DB_HOST4', 'DB_PORT4', 'DB_DATABASE4'),
            'username' => env('DB_USERNAME4', 'forget'),
            'password' => env('DB_PASSWORD4', ''),
            'charset' => env('DB_CHARSET4', 'utf8'),

            // 配置从服务器
            'slaveConfig' => [
                'username' => env('DB_USERNAME5', 'forget'),
                'password' => env('DB_PASSWORD5', ''),
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => env('DB_CHARSET', 'utf8'),
            ],

            // 配置从服务器组
            'slaves' => [
                ['dsn' => env2dsn('DB_HOST5', 'DB_PORT5', 'DB_DATABASE5')],
            ],
        ],
        
        'db6' => [
            'class' => 'yii\db\Connection',
            'dsn' => env2dsn('DB_HOST6', 'DB_PORT6', 'DB_DATABASE6'),
            'username' => env('DB_USERNAME6', 'forget'),
            'password' => env('DB_PASSWORD6', ''),
            'charset' => env('DB_CHARSET6', 'utf8'),
            
            // 配置从服务器
            'slaveConfig' => [
                'username' => env('DB_USERNAME7', 'forget'),
                'password' => env('DB_PASSWORD7', ''),
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => env('DB_CHARSET', 'utf8'),
            ],
            
            // 配置从服务器组
            'slaves' => [
                ['dsn' => env2dsn('DB_HOST5', 'DB_PORT5', 'DB_DATABASE5')],
            ],
        ],

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname'      => env('REDIS_HOST', 'localhost'),
            'port'          => env('REDIS_PORT', 6379),
            'database'      => env('REDIS_DATABASE', 0),
            'password'      => env('REDIS_PASSWORD', '')
        ],

        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' =>  env('MONGODB_CONNECTION', '')
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
