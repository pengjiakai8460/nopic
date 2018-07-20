<?php

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,

    'ftm_conf' => [ # 资金池配置
        /* 登录配置 */
        'login_history_items' => 5,                        //登录历史记录条数
        'login_session_expire' => 600,                    //登录过期时间,1周

        /* 日志服务 */
        'log_business_path' => 'F:/flycatlog/',                    //日志目录，
        'log_business_type' => 'sms,send,email,pay',    //日志类型，sms-短信平台(渠道所有发送日志)、send-短信发送(业务成功发送日志)

    ],
    'oss' =>[
        'AccessKeyId' => 'LTAIUsUkhIa68cs9', // AccessKeyId
        'AccessKeySecret' => 'JHH82zsFC3xuJb8ZsQc652C1PLoiEg', // AccessKeySecret
        'Bucket' => 'xmyj', // Bucket
        //'Endpoint' => 'oss-cn-hangzhou.aliyuncs.com', // Endpoint设置,杭州节点
        /* 经典网络 */
        //'Endpoint' => 'oss-cn-shanghai-internal.aliyuncs.com', //Endpoint设置,杭州内网节点，如果ECS服务器也是杭州节点，请使用该内网节点，节省流量和较快的上传速度
        /* 专有网络   参见: https://help.aliyun.com/document_detail/31837.html?spm=5176.7839687.6.569.wh94Al */
        'Endpoint' => 'vpc100-oss-cn-shanghai.aliyuncs.com',
        'Request_Uri' => 'http://xmyj.oss-cn-shanghai-internal.aliyuncs.com/', // 文件内网访问的url，内网访问不走流量，速度更快。
        'Request_Url' => 'http://xmyj.oss-cn-shanghai.aliyuncs.com/'
    ],
];

