<?php

namespace common\models\sms;

include_once __DIR__.'/../aliyun-php-sdk-core/Config.php';
include_once __DIR__.'/Sms/Request/V20160927/SingleSendSmsRequest.php';

use Sms\Request\V20160927\SingleSendSmsRequest;
use Yii;

class AliyunSms implements SmsInterface
{
    const DEFAULT_ALIYUN_SMS_CONFIG = [
        'regionId' => 'cn-hangzhou',
        'accessKeyId' => 'LTAIeaESicTURUS9',
        'accessSecret' => 'DUbxGuMwgYakjscALH2KwqBV7xRq13',
    ];
    const DEFAULT_TPL_ID = 'SMS_16730708';
    const DEFAULT_PLATFORM = '闪电鱼';

    public function send($phone, $tpl, $params, $platform)
    {
        if (empty($tpl)) {
            $tpl = self::DEFAULT_TPL_ID;
        }

        if (empty($platform)) {
            $platform = self::DEFAULT_PLATFORM;
        }

        // 因为阿里云需要数字转为字符串，此处保证一下
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                if (is_numeric($val)) {
                    $params[$key] = strval($val);
                }
            }
        }

        $aliSmsProfile = self::DEFAULT_ALIYUN_SMS_CONFIG;
        if (isset(Yii::$app->params['aliyun']['sms'])) {
            $aliSmsProfile = Yii::$app->params['aliyun']['sms'];
        }
        
        $iClientProfile = \DefaultProfile::getProfile($aliSmsProfile['regionId'], $aliSmsProfile['accessKeyId'], $aliSmsProfile['accessSecret']);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new SingleSendSmsRequest();

        $request->setSignName($platform);/*签名名称*/
        $request->setTemplateCode($tpl);/*模板code*/
        $request->setRecNum($phone);/*目标手机号*/
        $request->setParamString(json_encode($params));/*模板变量，数字一定要转换为字符串*/

        try {
            $response = $client->getAcsResponse($request);
            return get_object_vars($response);
        }
        catch (\ClientException $e) {
            return ['suc' => false, 'detail' => [$e->getErrorType(), $e->getErrorCode(), $e->getErrorMessage()]];
        }
    }
}