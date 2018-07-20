<?php
/**
 * Created by PhpStorm.
 * User: chenhc
 * Date: 2016/12/25
 * Time: 下午11:28
 */

namespace Addons\Aliyun\SMS;
require_once __DIR__.'/SDK/sdk_core/Config.php';
class sendSMS{

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

    public function sendSMS($templateCode , $phone , $codeArr){
        $iClientProfile = \DefaultProfile::getProfile("cn-shanghai", $this->_accessKeyId, $this->_accessSecret);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new \SingleSendSmsRequest();
        $request->setSignName($this->_siginName);    /*签名名称*/
        $request->setTemplateCode($templateCode);/*模板code*/

        $request->setRecNum( is_array($phone) ? trim(implode(',',$phone),',') : $phone );/*目标手机号*/
        $request->setParamString(json_encode($codeArr));/*模板变量，数字一定要转换为字符串*/

        try {
            $result = $client->getAcsResponse($request);
            return $result;
        }
        catch (ClientException  $e) {
            $result['clientErrorCode'] = $e->getErrorCode();
            $result['clientErrorCode'] = $e->getErrorMessage();
            return $result;
        }
        catch (ServerException  $e) {
            $result['serverErrorCode'] = $e->getErrorCode();
            $result['serverErrorCode'] = $e->getErrorMessage();
            return$result;
        }
    }
    
    
    protected function _setError($error){
        $this->_error = $error;
    }

    public function getError(){
        return $this->_error;
    }
}