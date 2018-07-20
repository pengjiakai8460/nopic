<?php
return [
    'adminEmail' => 'admin@example.com',

    //忽略登录验证的url
    'without_login' => [
        'api'=>[//模块名
            'pay/pay'=>[//这里是中间的控制器名
                '*'//访问的方法名，*表示所有方法不需要验证登录
            ],
            'course/course'=>[//这里是中间的控制器名
                '*'//访问的方法名，*表示所有方法不需要验证登录
            ],
            'course/video'=>[//这里是中间的控制器名
                '*'//访问的方法名，*表示所有方法不需要验证登录
            ],
            'course/homework'=>[//这里是中间的控制器名
                '*'//访问的方法名，*表示所有方法不需要验证登录
            ],
            'index/compose' => [
                'save-homework'
            ],
            'course/users' => [
                'test',
                'callback'
            ],
            'classes/index' => [
                'wechat-play-course-video'
            ],
            'index/index' => [
                '*'
            ],
            'index/test' => [
                '*'
            ]

        ],
        'admin' => [
            'index/index'=>[
                'login'
            ],
            'index/compose'=>
            [
                'save-homework'
            ],
            'index/statistics' => [ //关于统计的控制器方法
                '*'
            ],
            'course/video' => [
                '*',
            ],
            'classes/index' => [
                '*'
            ]
        ]
    ],
];
