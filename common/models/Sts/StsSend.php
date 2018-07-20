<?php

namespace common\models\Sts;


use Sts\Request\V20150401\AssumeRoleRequest;

include_once __DIR__ . '/../aliyun-php-sdk-core/Config.php';
include_once __DIR__ . '/Request/V20150401/AssumeRoleRequest.php';


class StsSend
{

    public static function send($sessionName, $duration)
    {
        // 你需要操作的资源所在的region，STS服务目前只有杭州节点可以签发Token，签发出的Token在所有Region都可用
        // 只允许子用户使用角色
        $accessKeyId = \Yii::$app->params['aliyun']['oss']['subOss']['accessKeyId'];
        $accessKeySecret = \Yii::$app->params['aliyun']['oss']['subOss']['accessKeySecret'];
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessKeySecret);
        $client = new \DefaultAcsClient($iClientProfile);

        // 角色资源描述符，在RAM的控制台的资源详情页上可以获取
        $roleArn = \Yii::$app->params['aliyun']['oss']['subOss']['roleArn'];


        // 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
        // 详情请参考《RAM使用指南》
        // 此授权策略表示读取所有OSS的只读权限
        $policy = <<<POLICY
                {
                  "Statement": [
                    {
                      "Action": [
                        "oss:PutObject"
                      ],
                      "Effect": "Allow",
                      "Resource": [
                            "acs:oss:*:*:shandianyu-h5/*"
                            ]
                
                    }
                  ],
                  "Version": "1"
                }
POLICY;

        $request = new AssumeRoleRequest();
        // RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
        // 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName($sessionName);
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds($duration);
        $response = $client->doAction($request);
        return $response;
    }
}

?>