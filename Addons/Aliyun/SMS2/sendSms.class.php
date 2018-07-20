<?php
/*
 * 此文件用于验证短信服务API接口，供开发时参考
 * 执行验证前请确保文件为utf-8编码，并替换相应参数为您自己的信息，并取消相关调用的注释
 * 建议验证前先执行Test.php验证PHP环境
 *
 * 2017/11/30
 */

namespace Addons\Aliyun\SMS2;

class sendSms{

    private $_accessKeyId ;
    private $_accessSecret ;
    private $_siginName ;
    private $_error;

    public function __construct( $defultconfig = array() ){
        $Config = $defultconfig ? $defultconfig : require_once __DIR__.'/config.php';
        if ( !$Config['accessKeyId'] || !$Config['accessSecret'] || !$Config['siginName']){
            $this->_setError('未设置accessKeyId或accessSecret或siginName');
            return false;
        }
        $this->_accessKeyId = $Config['accessKeyId'];
        $this->_accessSecret = $Config['accessSecret'];
        $this->_siginName = $Config['siginName'];
    }

    /*
     * 发送短信
     */
    public function sendSMS($templateCode , $phone , $codeArr){

        $params = array ();
        if(is_array($phone)){
            if( count($phone) > 1000 ){
                //接口每次最多发送1000个
                $send_phone_arr = array_chunk($phone,1000);
                foreach ($send_phone_arr as $send_phone_vo){
                    $this->sendSMS($templateCode , $send_phone_vo , $codeArr);
                }
            }else{
                $send_phone = trim(implode(',',trim($phone)),',');
            }
        }else{
            $send_phone = $phone;
        }
        // *** 需用户填写部分 ***
        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $send_phone;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $this->_siginName;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = $codeArr;
        
        // fixme 可选: 设置发送短信流水号
        //$params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new \Addons\Aliyun\SMS2\SDK\SignatureHelper();

        // 此处可能会抛出异常，注意catch
        try{
            $result = $helper->request(
                $this->_accessKeyId,
                $this->_accessSecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ))
            );
            //记录发送日志
            M('LogPhonecode')->add(array('phone'=>$send_phone,'request'=>json_encode($params),'response'=>json_encode($result),'add_time'=>time()));
            return $result;
        }catch (\Exception $e) {
            //什么都不做
            return '';
        }
    }

    protected function _setError($error){
        $this->_error = $error;
    }

    public function getError(){
        return $this->_error;
    }
}


/**
 * 发送短信
 */
/*
function sendSms() {

    $params = array ();

    // *** 需用户填写部分 ***

    // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
    $accessKeyId = "your access key id";
    $accessKeySecret = "your access key secret";

    // fixme 必填: 短信接收号码
    $params["PhoneNumbers"] = "17000000000";

    // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    $params["SignName"] = "短信签名";

    // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    $params["TemplateCode"] = "SMS_0000001";

    // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
    $params['TemplateParam'] = Array (
        "code" => "12345",
        "product" => "阿里通信"
    );

    // fixme 可选: 设置发送短信流水号
    $params['OutId'] = "12345";

    // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
    $params['SmsUpExtendCode'] = "1234567";


    // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }

    // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
    $helper = new SignatureHelper();

    // 此处可能会抛出异常，注意catch
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ))
    );

    return $content;
}

ini_set("display_errors", "on"); // 显示错误提示，仅用于测试时排查问题
set_time_limit(0); // 防止脚本超时，仅用于测试使用，生产环境请按实际情况设置
header("Content-Type: text/plain; charset=utf-8"); // 输出为utf-8的文本格式，仅用于测试

// 验证发送短信(SendSms)接口
print_r(sendSms());
*/