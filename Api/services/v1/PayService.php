<?php

namespace Api\services\v1;

use Yii;
use yii\base\Controller;
use Aliyun\baichuan;
use Aliyun\baichuan\top\HttpdnsGetRequest;
use Aliyun\baichuan\top\TopClient;
use Aliyun\baichuan\top\ClusterTopClient;
use Aliyun\baichuan\top\request\OpenSmsSendmsgRequest;
use Aliyun\baichuan\top\domain\SendVerCodeRequest;
use Aliyun\baichuan\top\domain\SendMessageRequest;
use common\models\utils\StringUtil;
use yii\base\Object;
use common\models\orm\VerifyCode;
use common\models\orm\PayGatewayNotify;
use common\models\orm\PayCallbackFailure;
use beecloud\rest\api;
use common\models\orm\MemberPayRecode;
use common\models\orm\MemberWealth;
use common\models\orm\Buyorder;
use common\models\service\CapitalaaService;
use common\base\BaseService;
use Api\services\v1\MongoService;





class PayService extends BaseService
{

    const PER_PAGE_SIZE = 10;

    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     *
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author zhangzhicheng
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            // $model->_md=new CActiveRecordMetaData($model);
            // $model->attachBehaviors($model->behaviors());
            return $model;
        }
    }

    /**
     * 写入充值记录表
     * @param array $data
     * @return boolean
     */
    public function payGatewayNotify($data)
    {

        //exit(var_dump($data));
        //写入返回的数据@todo
        $adddate = new PayGatewayNotify();
        //传入的是对象
        if (is_object($data)) {

            $adddate->is_success = $data->messageDetail->trade_status == "TRADE_SUCCESS" ? 'T' : 'F';
            $adddate->notify_id = $data->messageDetail->notify_id;
            $adddate->notify_time = $data->messageDetail->notify_time;
            $adddate->notify_type = $data->messageDetail->notify_type;
            $adddate->out_trade_no = $data->messageDetail->out_trade_no;
            //$adddate->payment_type = $data->messageDetail->payment_type;  //回调结果中无此项
            $adddate->seller_id = $data->messageDetail->seller_id;
            //$adddate->service = $data['service'];                         //回调结果中无此项
            $adddate->subject = $data->messageDetail->subject;
            $adddate->total_fee = $data->messageDetail->total_fee;
            $adddate->trade_no = $data->messageDetail->trade_no;
            $adddate->trade_status = $data->messageDetail->trade_status;
            $adddate->sign = $data->sign;
            $adddate->sign_type = $data->messageDetail->sign_type;
        } //转入的是数组
        else if (is_array($data)) {

            $adddate->is_success = $data['is_success'];
            $adddate->notify_id = $data['notify_id'];
            $adddate->notify_time = $data['notify_time'];
            $adddate->notify_type = $data['notify_type'];
            $adddate->out_trade_no = $data['out_trade_no'];
            $adddate->payment_type = $data['payment_type'];
            $adddate->seller_id = $data['seller_id'];
            $adddate->service = $data['service'];
            $adddate->subject = $data['subject'];
            $adddate->total_fee = $data['total_fee'];
            $adddate->trade_no = $data['trade_no'];
            $adddate->trade_status = $data['trade_status'];
            $adddate->sign = $data['sign'];
            $adddate->sign_type = $data['sign_type'];
        } else {
            //参数错误
            return false;
        }
        //exit(print_r($adddate->attributes));
        $result = $adddate->save(false);
        //exit($result);
        if ($result) return true;
        else return false;
    }

    /**
     * 写入银联充值记录表
     * @param array $data
     * @return boolean
     */
    public function payNuGatewayNotify($data)
    {
        $adddate = new PayGatewayNotify();
        //传入的是对象
        if (is_object($data)) {
            $adddate->is_success = $data->messageDetail->respCode == "00" ? 'T' : 'F';    //交易状态
            $adddate->out_trade_no = $data->messageDetail->orderId;                       //内部交易号
            $adddate->trade_no = $data->messageDetail->traceNo;                           //银联交易号
            $adddate->settle_amt = $data->messageDetail->settleAmt;                       //清算金额
            $adddate->settle_currency_code = $data->messageDetail->settleCurrencyCode;    //清算币种
            $adddate->txn_type = $data->messageDetail->txnType;                           //交易类型
            $adddate->query_id = $data->messageDetail->queryId;                            //查询流水
            $adddate->total_fee = $data->messageDetail->txnAmt;                           //商品总价
            $adddate->sign = $data->sign;                                                 //签名
            $adddate->sign_type = $data->messageDetail->signMethod;                       //签名方法
            $adddate->created = time();
        } else {
            //参数错误
            return false;
        }
        $result = $adddate->save(false);
        if ($result)
            return true;
        else
            return false;
    }


    /**
     * 写入充值失败记录表
     * @param unknown $total_fee
     * @param string $return_url
     * @param string $bill_no
     * @param string $title
     * @param string $currency
     * @param string $credit_card_info
     * @param string $credit_card_id
     * @param string $optional
     */
    public function payCallbackFailure(array $data)
    {

        //exit(print_r($data));
        //写入返回的数据@todo
        $notify = PayGatewayNotify::findOne(['trade_no' => $data['trade_no']]);
        $adddate = new PayCallbackFailure();
        $adddate->notify_id = $notify->id;
        $adddate->order_no = $notify->trade_no;
        $adddate->pay = $notify->total_fee;
        $adddate->created = time();
        //exit(print_r($adddate));
        $result = $adddate->save(false);
        //exit($result);
        if ($result) return true;
        else return false;
    }

    public function beecloudAliPay($total_fee, $return_url = null, $bill_no = null, $title = null, $currency = null, $credit_card_info = null, $credit_card_id = null, $optional = null)
    {
//         $return_url= 'http://192.168.0.111/site/user/bankcashrecharge';
        return $this->beecloudPay('ALI_WAP', $total_fee, $return_url, $bill_no, $title, $currency, $credit_card_info, $credit_card_id, $optional);
    }

    public function beecloudUnPay($total_fee, $return_url = null, $bill_no = null, $title = null, $currency = null, $credit_card_info = null, $credit_card_id = null, $optional = null)
    {
        return $this->beecloudPay('UN_WEB', $total_fee, $return_url, $bill_no, $title, $currency, $credit_card_info, $credit_card_id, $optional);
    }

    public function beecloudPay($channel, $total_fee, $return_url = null, $bill_no = null, $title = null, $currency = null, $credit_card_info = null, $credit_card_id = null, $optional = null, $timestamp = null, $wxopenid = null)
    {

        $payConfig = array();
        $payConfig['timestamp'] = empty($timestamp) ? (time() * 1000) : $timestamp;
        $payConfig['app_id'] = Yii::$app->params['paymentGateway']['beecloud']['appID'];
        $payConfig['app_sign'] = md5($payConfig["app_id"] . $payConfig["timestamp"] . Yii::$app->params['paymentGateway']['beecloud']['appSecret']);
        $payConfig["channel"] = empty($channel) ? "ALI_WAP" : $channel;
        $payConfig['total_fee'] = intval($total_fee * 100); //@todo float
        $payConfig['currency'] = $currency;
        $payConfig['bill_no'] = empty($bill_no) ? ("YB-test-bill-no_" . $payConfig["timestamp"]) : $bill_no;
        $payConfig['title'] = empty($title) ? 'YB支付测试订单' : $title;
        $payConfig['credit_card_info'] = $credit_card_info;
        $payConfig['credit_card_id'] = $credit_card_id;
        $payConfig["return_url"] = empty($return_url) ? "ALI_WEB" : $return_url;
        $payConfig['channel'] = $channel;

        if ($channel == 'WX_JSAPI')
            $payConfig["openid"] = $wxopenid;
//                 exit($wxopenid);
        $payConfig['title'] = substr($payConfig['title'], 0, 32);

        //生成充值订单数据
        //var_dump($payConfig);exit;

        $payConfig["optional"] = json_decode(json_encode(array("tag" => "msgtoreturn")));
        return $this->beecloudPaymentGateway($payConfig);
    }


    public function beecloudPaymentGateway(array $payConfig)
    {

        $data = array();
        $data['timestamp'] = time() * 1000;
        $data['app_id'] = Yii::$app->params['paymentGateway']['beecloud']['appID'];
        $data['app_sign'] = md5($data["app_id"] . $data["timestamp"] . Yii::$app->params['paymentGateway']['beecloud']['appSecret']);
        //在beecloudPay()方法中根据传入的$channel参数动态赋值channel在payConfig数组中，此处无需再赋值
        //$data["channel"] = "ALI_WAP";
        $data['total_fee'] = $payConfig['total_fee'];
        $data['currency'] = '';
        //$data['bill_no'] =  "bcdemo" . $data["timestamp"];
        $data['title'] = '支付测试订单';
        $data['credit_card_info'] = '';
        $data['credit_card_id'] = '';
        $data["optional"] = json_decode(json_encode(array("tag" => "msgtoreturn")));
        $data = array_merge($payConfig, $data);
        // exit(var_dump($data));

        if ($data['channel'] == 'WX_JSAPI') {

            $data['channel'] = 'BC_WX_JSAPI';
            $data['instant_channel'] = 'bcwx';
        }

        try {
            //使用支付接口
            //var_dump($data);
            $result = api::bill($data);
            //exit(print_r($result));
            if ($result->result_code != 0) {

                return $result;
                echo json_encode($result);
                exit();
            }
//             echo "-------------------------</br>";
//             var_dump(htmlspecialchars($result->html));
//             exit("111");
            if ($data['channel'] == 'WX_JSAPI') {
                $jsApiParam = array("appId" => $result->app_id,
                    "timeStamp" => $result->timestamp,
                    "nonceStr" => $result->nonce_str,
                    "package" => $result->package,
                    "signType" => $result->sign_type,
                    "paySign" => $result->pay_sign);

                $html = '<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
                    <script type="text/javascript">
                               callpay();
                               function callpay() {
                                    if (typeof WeixinJSBridge == "undefined"){
                                        if( document.addEventListener ){
                                            document.addEventListener(\'WeixinJSBridgeReady\', jsApiCall, false);
                                        }else if (document.attachEvent){
                                            document.attachEvent(\'WeixinJSBridgeReady\', jsApiCall);
                                            document.attachEvent(\'onWeixinJSBridgeReady\', jsApiCall);
                                        }
                                    }else{
                                        jsApiCall();
                                    }
                                }
                                //调用微信JS api 支付
                                function jsApiCall() {
                                    WeixinJSBridge.invoke(
                                        \'getBrandWCPayRequest\',
                                        ' . json_encode($jsApiParam) . ',
                                        function(res){
//                                             WeixinJSBridge.log(res.err_msg);
//                                             alert(res.err_code);
//                                             alert(res.err_desc);
//                                             alert(res.err_msg);
                                            if(res.err_msg == "get_brand_wcpay_request:ok" ){
//                                                 alert("支付成功");
                                                location.href="/site/mypartner/paycallback?order_no=' . $data['bill_no'] . '&is_success=T&pay=' . $data['total_fee'] . '";
//                                              alert("' . $data['bill_no'] . '");
                                            }else{
                                                  alert("支付失败");
                                                  location.href="/site/mypartner/paycallback?order_no=' . $data['bill_no'] . '&is_success=F&pay=' . $data['total_fee'] . '";
                                            }

                                        }
                                    );
                                }

                            </script>';
                echo $html;
                exit();
            } else {
                $htmlContent = $result->html;
                echo $htmlContent;
                return;
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    /**
     * 生成一个支付订单号
     * $type  类型 默认为1  为充值的 订单   2 为代理缴费的充值订单 3 为购买金币 4 购物
     * return: string
     */
    public function createdOrderNo($type = 1)
    {

        $chars = '0123456789';
        if ($type == 4) {
            $str = 'GOODS' . time();
        } else if ($type == 3) {
            $str = 'GMORDER' . time();
        } else if ($type == 2) {
            $str = 'DLORDER' . time();
        } else {
            $str = 'YBORDER' . time();
        }
        for ($i = 0; $i < 10; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }


    /**
     * beecloud 将成功支付的订单信息存入 msyql 的记录表中
     * @param      object $data 对象化的回调信息
     * @param      array $myorder 我方数据库中的订单信息
     * @return     bool                   记录成功 true , 失败 false
     */
    public static function recordSuccessOrder($data, $myorder, $oldbalacnce = null, $newbalacnce = null)
    {
        if (is_array($data)) {
            $data = (object)$data;
        }
        if (isset($data->channel_type) && $data->channel_type == 'BC' && isset($data->message_detail->trade_no)) {
            $data->transaction_id = $data->message_detail->trade_no . '_' . $data->transaction_id;
        }
        $m = new MemberPayRecode;
        $m->uid = (int)$myorder->uid;                      # 用户ID
        $m->oid = (string)$myorder->order_id;             # 订单ID
        $m->money = floatval($myorder->money);    # 订单金额
        $m->created_time = (int)$myorder->createtime;      # 订单创建时间
        $m->pay_method = isset($data->sub_channel_type) ? $data->sub_channel_type : $data->channel_type;        # 支付平台代号，ALI 支付宝 WX 微信 UN 银联
        $m->completed_time = time();                         # 订单完成时间
        $m->oldbalacnce = !empty($oldbalacnce) ? $oldbalacnce : 0;
        $m->newbalacnce = !empty($newbalacnce) ? $newbalacnce : 0;
        if (isset($data->created) && ($data->channel_type == 'ALI' || $data->channel_type == 'WX' || $data->channel_type == 'UN')) {
            $m->pay_time = (string)($data->created) ? (string)($data->created) : strval(time());  # 交易时间
        } else {
            $m->pay_time = (string)time();
        }
        $m->businesstype = $myorder->ordername;         # 订单业务类型

        self::recordOrder($data, '支付成功', $myorder, 0, $oldbalacnce, $newbalacnce);       # 插入mongo
        return $m->save();
    }

    /**
     *Apple内购回调  订单信息成功充值后 mysql记录表入库操作
     * $uid int 用户id
     * $oid string 订单id
     * $money decimal 订单资金
     * $order_createtime 订单创建时间
     * $completed_time 订单完成时间
     */
    public function recordAppleSuccessOrder($uid, $oid, $money, $balance, $order_createtime)
    {
        $state = 1;
        $pay_method = 'Apple';
        $m = new MemberPayRecode;
        $m->uid = (int)$uid;                      # 用户ID
        $m->oid = (string)$oid;             # 订单ID
        $m->money = floatval($money);    # 订单金额
        $m->created_time = (int)$order_createtime;      # 订单创建时间
        $m->pay_method = (string)$pay_method;        # 支付平台代号，ALI 支付宝 WX 微信 UN 银联
        $m->completed_time = time();                         # 订单完成时间
        $m->state = $state;       #订单完成
        $m->oldbalacnce = $balance;  #账号的原有余额
        $m->businesstype = '闪电鱼用户充值';
        return $m->save();
    }

    /**
     * beecloud 将回调信息存入 mongodb 记录表中
     * @param       object $data 对象化的回调信息
     * @param       string $info 错误信息
     * @param       array $myorder 我方数据库中的订单信息
     * @return      null
     */
    public static function recordOrder($data, $info = null, $myorder = null, $status = 0, $oldbalacnce = null, $newbalacnce = null)
    {
        if (is_array($data)) {
            $data = (object)$data;
        }
        $order['subchannel'] = isset($data->sub_channel_type) ? $data->sub_channel_type : $data->channel_type;               # 交易渠道子渠道，例如 ALI 支付宝 子渠道 ALI_WAP手机支付宝
        $order['orderid'] = $data->transaction_id;                    # 支付订单ID
        $order['uid'] = !empty($myorder['uid']) ? $myorder['uid'] : null;  # 用户ID
        $order['error'] = $info;                                           # 错误信息
        $order['businesstype'] = !empty($myorder['businesstype']) ? $myorder['businesstype'] : null; # 订单业务类型
        $order['createtime'] = !empty($myorder['createtime']) ? $myorder['createtime'] : null;       # 订单创建时间
        $order['oldbalacnce'] = !empty($oldbalacnce) ? $oldbalacnce : 0;
        $order['newbalacnce'] = !empty($newbalacnce) ? $newbalacnce : 0;
        if ($data->channel_type == 'ALI') {
            $order['paytime'] = isset($data->message_detail->gmt_create) ? strtotime($data->message_detail->gmt_create) : '';  # 交易时间
            $order['tradenumber'] = isset($data->message_detail->trade_no) ? $data->message_detail->trade_no : '';       # 支付宝交易号
            $order['buyerid'] = isset($data->message_detail->buyer_id) ? $data->message_detail->buyer_id : '';           # 买家支付宝唯一用户号
            $order['buyeraccount'] = isset($data->message_detail->buyer_email) ? $data->message_detail->buyer_email : '';   # 买家支付宝账号
        } else if ($data->channel_type == 'WX' || $data->channel_type == 'BC') {
            $order['paytime'] = isset($data->message_detail->time_end) ? strtotime($data->message_detail->time_end) : '';            # 交易时间
            $order['tradenumber'] = isset($data->message_detail->transaction_id) ? $data->message_detail->transaction_id : '';  # 微信交易号
            $order['buyerid'] = isset($data->message_detail->openid) ? $data->message_detail->openid : '';              # 买家微信openid
        } else if ($data->channel_type == 'UN') {
            $order['paytime'] = strtotime($data->message_detail->txnTime);      # 交易时间
        }
        $order['channel'] = $data->channel_type;                     # 支付平台的子平台代号 如 ALI_WAP ALI_WEB
        $order['money'] = floatval($data->transaction_fee / 100);      # 支付金额
        $order['status'] = $status;                                  # 0 成功  1失败
        if (!MongoService::selectOne('paylog', $order)) {
            MongoService::insert('paylog', $order);              # 插入 mongo 记录表
        } else {
            MongoService::insert('paylog_repeat', $order);              # 插入 mongo 记录表
        }

    }

    //支付回调
    public function paymentCallback($myorder, $data) {
        $buyorder = $myorder;

        $oldbalacnce = MemberWealth::getBalance($buyorder['uid']);
        if (!empty($data->transaction_fee)) {
            $buyorder->money = bcdiv($data->transaction_fee, 100, 2);       # 充值订单以成功支付金额为准
        }
        $buyorder->status = 4;
        $buyorder->ispay = 1;        # 订单状态 已付款
        $buyorder->paytime = time();
        $error = '';
        $transaction = Yii::$app->db->beginTransaction();
        $ma = new MemberWealth();
        $mBanlance1 = $ma->getBalance($buyorder->uid);
        $result = false;
        try {
            if (!$buyorder->save()) {
                $error = '订单修改不成功';
                throw new \Exception('live_order表status数据更新不成功');
            }
            if (!PayService::model()->recordSuccessOrder($data, $buyorder, $oldbalacnce)) {
                throw new \Exception('充值日志操作失败');
            }
            if (!CapitalaaService::model()->charge($buyorder->uid, $buyorder->id, $buyorder->money, $mBanlance1)) {
                $error = '资金操作失败';
                throw new \Exception('资金操作失败');
            };
            $result = true;
            //提交事务
            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollback();
            MongoService::model()->insert('debug_live_order_e', [
                'error' => $error,
                'errorsCode' => $e->getCode(),
                'errorsFile' => $e->getFile(),
                'errorsLine' => $e->getLine(),
                'errorsMessage' => $e->getMessage(),
                'errorsTraceAsString' => $e->getTraceAsString(),
                'createtime' => time(),
                'order_id' => $buyorder->order_id,
            ]);
        }

        return $result;

    }

    public function getBuyOrder($transaction_id) {
        return Buyorder::findOne(['order_id' => $transaction_id]);
    }
}
