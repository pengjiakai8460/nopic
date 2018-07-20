<?php
namespace Course\modules\site\controllers;

use common\base\BaseController;

class IndexController extends BaseController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'common\base\exception\LogicErrorAction',
            ],
        ];
    }

    public function actionTest()
    {

    	ini_set('date.timezone','Asia/Shanghai');
		//error_reporting(E_ERROR);
		require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/lib/WxPay.Api.php";
		require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/example/WxPay.JsApiPay.php";
		require_once '../../vendor/WechatPay/WxpayAPI_php_v3.0.1/example/log.php';	
		require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/lib/WxPay.Data.php";	
		/*require_once '../../vendor/wxpay/lib/WxPay.Api.php';
		require_once "../../vendor/wxpay/example/WxPay.JsApiPay.php";
		require_once '../../vendor/wxpay/lib/WxPay.Data.php';*/
		//初始化日志
		/*$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 15);*/

		/*//打印输出数组信息
		function printf_info($data)
		{
		    foreach($data as $key=>$value){
		        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
		    }
		}*/

		//①、获取用户openid
		$tools = new \JsApiPay();
		//var_dump($tools);exit();
		$openId = $tools->GetOpenid();
//        $openId = 'ocltUwgboR7Q_xLS1g85h3n0qa48';
//		echo $openId;exit();
		//②、统一下单
		$input = new \WxPayUnifiedOrder();
		$input->SetBody("test");
		$input->SetAttach("test");
		$input->SetOut_trade_no('Course'.date("YmdHis"));
		$input->SetTotal_fee("1");
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("alipay.xiaoma.cn/alipay.php/Alipay/wechatcoursenotifyurl");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = \WxPayApi::unifiedOrder($input);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		return $this->result(200, '微信下单成功, 返回签名', $jsApiParameters);
    }
}