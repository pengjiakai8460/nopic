<?php
return [
    'adminEmail' => 'admin@example.com',

    'without_login' => [
        'school/user/login',
        'school/user/auth',
        'admin/school/create',
        'admin/school/list',
        'admin/school/findone',
        'admin/section/findone',
        'admin/section/create',
        'admin/section/update',
        'admin/section/create',
        'admin/section/delete',
        'admin/material/findone',
        'admin/material/list',
        'admin/material/create',
        'admin/material/update',
        'admin/material/delete',
        'admin/lesson/findone',
        'admin/lesson/list',
        'admin/lesson/update',
        'admin/lesson/create',
        'admin/lesson/delete',
        'admin/course/findone',
        'admin/course/create',
        'admin/course/update',
        'admin/course/list',
        'admin/course/delete',
        
    ],

    // 频率控制
    'throttle' => [
//        'member/user',
    ],

    'api_4_third_party' => [

    ],
];
