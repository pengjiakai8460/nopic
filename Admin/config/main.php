<?php
use yii\widgets\LinkPager;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-Admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'Admin\controllers',
    'defaultRoute' => '/question/question/index',
    'bootstrap' => ['log', 'debug'],

    'runtimePath' => env('ADMIN_RUNTIME_PATH', 'assets'),
    'layout' => '@Admin/modules/layouts/main.twig',
    'modules' => [
        'account' => [//管理员
            'class' => 'Admin\modules\account\Account',
        ],
        'manage' => [//
            'class' => 'Admin\modules\manage\Manage',
        ],
        'users' => [
            'class' => 'Admin\modules\users\Users',
        ],
        'question' => [
            'class' => 'Admin\modules\question\Question',
        ],
        'tag' => [
            'class' => 'Admin\modules\tag\Tag',
        ],
        'feedback' => [
            'class' => 'Admin\modules\feedback\Feedback',
        ],
        'task' => [
            'class' => 'Admin\modules\task\Task',
        ],
        'exams' => [
            'class' => 'Admin\modules\exams\Exams'
        ],
        'gii' => [
            'class' => 'yii\gii\Module',
            'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*','192.168.1.*']
        ],
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*','192.168.1.*']
        ],

    ],

    'components' => [
        'session'      => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname'  => env('SESSION_HOST', 'localhost'),
                'port'      => env('SESSION_PORT', 6379),
                'database'  => env('SESSION_DATABASE', 12),
                'password'  => env('SESSION_PASSWORD', null),
            ],
            'timeout'=> env('SESSION_TIMEOUT', 86400),
        ],
        'request' => [
            'enableCsrfValidation'   => false,
            'enableCookieValidation' => false,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // or use 'yii\rbac\DbManager'
        ],
        'user' => [
            'identityClass' => 'common\models\orm\Member',
            'enableAutoLogin' => true,
        ],

        'errorHandler' => [
            'errorAction' => 'manage/manager/error',
        ],

        'view' => [
            'class' => 'yii\web\View',
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    // set cachePath to false in order to disable template caching
                    'cachePath' => '@runtime/Twig/cache',
                    // Array of twig options:
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => [
                        'html' => '\yii\helpers\Html',
                        'name' => 'Carsten',
                        'GridView' => '\yii\grid\GridView',
                    ],
                    'uses' => ['yii\bootstrap'],

                    'functions' => [
                        'pageTitle'=>function() {
                            return Yii::$app->title;
                        },

                        'var_dump' => 'var_dump',
                        'rot13' => 'str_rot13',
                        'truncate' => '\yii\helpers\StringHelper::truncate',
                        new \Twig_SimpleFunction((string)'rot14', (string)'str_rot13'),
                        new \Twig_SimpleFunction('add_*', function ($symbols, $val) {
                            return $val . $symbols;
                        }, ['is_safe' => ['html']]),
                        'callable_add_*' => function ($symbols, $val) {
                            return $val . $symbols;
                        },
                        'sum' => function ($a, $b) {
                            return $a + $b;
                        },
                        'implode' => function($data){
                            return implode(',',$data);
                        },
                        'LinkPager' => function($pages){
                            return   LinkPager::widget([
                                'pagination' => $pages,
                                'lastPageLabel'=>'尾页',
                                'firstPageLabel'=>'首页',
                                'prevPageLabel' => '上一页',
                                'nextPageLabel' => '下一页',
                            ]);
                        },

                    ],

                    'filters' => [
                        'jsonEncode' => '\yii\helpers\Json::htmlEncode',
                        new \Twig_SimpleFilter('rot13', 'str_rot13'),
                        new \Twig_SimpleFilter('add_*', function ($symbols, $val) {
                            return $val . $symbols;
                        }, ['is_safe' => ['html']]),
                        'callable_rot13' => function($string) {
                            return str_rot13($string);
                        },
                        'callable_add_*' => function ($symbols, $val) {
                            return $val . $symbols;
                        }
                    ],
                ],
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    'cachePath' => '@runtime/Smarty/cache',
                ],
                // ...
            ],
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];
