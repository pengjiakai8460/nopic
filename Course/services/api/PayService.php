<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmUsers;
use common\models\orm\XmVCourse;
use common\models\orm\XmVOrders;

class PayService extends BaseService
{
    private static $appid = 'wx60ab09a315faea22';
    private static $appsecret = '57465a0c044eff06f4f73aab04ae3b5e';

    private static $_models = array();
    /**
     * 初始化，每个Service都必须执行此方法
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
            return $model;
        }
    }


    //变更订单状态
    public static function saveOrderStatus($order_no)
    {
        $order = XmVOrders::find()->where(['orderSn'=>$order_no])->asArray()->one();
        if ($order['status'] == 'paid') {
            return 1;
        }
        $data = array('status'=> 'paid', 'paidTime'=>time());
        $ret = XmVOrders::updateAll($data, ['orderSn'=> $order_no]);
        if($ret){
            //支付回调成功后发送微信通知
            WechatService::sendTemplateMessage($order_no);
            return 1;
        }else{
            return 0;
        }
    }

    //生成用于微信内H5调起支付的签名数据
    public static function getSign($users_id, $openId, $course_id, $chn = 0)
    {
        ini_set('date.timezone','Asia/Shanghai');
        //error_reporting(E_ERROR);
        require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/lib/WxPay.Api.php";
        require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/example/WxPay.JsApiPay.php";
        require_once '../../vendor/WechatPay/WxpayAPI_php_v3.0.1/example/log.php';
        require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/lib/WxPay.Data.php";
        //生成订单号用于生成订单记录
        $order_no = 'C'.date("YmdHis").rand(10000, 99999);

        //这里获取课程价格等信息
        $price = self::getCoursePrice($course_id);

        //①、获取用户openid
        $tools = new \JsApiPay();
//        $openId = $tools->GetOpenid();
        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("小码世界少儿编程ScratchL1课程");
        $input->SetAttach("小码世界少儿编程ScratchL1课程");
        $input->SetOut_trade_no($order_no);
        $input->SetTotal_fee((string)$price);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("alipay.xiaoma.cn/alipay.php/Alipay/wechatcoursenotifyurl");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);
        self::addOrder($users_id, $order_no, $course_id, $price, $chn, 'wechat');
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return $jsApiParameters;
    }

    //生成订单的方法
    public static function addOrder($user_id, $order_no, $course_id, $price, $chn = 0, $payment = 'wechat')
    {
        $course = XmVCourse::find()->where(['id' => $course_id])->one();
        //生成方法
        $customer = new XmVOrders();
        $customer->orderSn = $order_no;
        $customer->courseId = $course_id;//课程id
        $customer->title = $course['title'];
        $customer->price = $price;
        $customer->userId = $user_id;
        $customer->status = 0;
        $customer->createdTime = time();
        $customer->status = 'draft';
        $customer->payment = $payment;
        $customer->chn = $chn;
        $customer->save();
        return $customer->id;
    }

    //获取js的config签名
    public static function getJsConfig($url)
    {
//        $ticket = RedisService::get('ticket');
//        if(empty($ticket)){
//            $ret = self::createJsConfig();
//            if(!$ret[0]){
//                return $ret[1];
//            }else{
//                $ticket = $ret[1];
//            }
//        }
        $ticket = self::createJsConfig();
        //生成config签名
        $noncestr = self::randomkeys(16);
        $jsapi_ticket = $ticket;
        $timestamp = time();
        $string1 = 'jsapi_ticket='.$jsapi_ticket[1].'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $string = sha1($string1);
        return ['appId'=> self::$appid, 'timestamp'=> $timestamp, 'nonceStr'=> $noncestr, 'signature'=>$string, 'ticket'=>$ticket];
    }

    //生成调用js的config签名
    private static function createJsConfig()
    {
        //读取redis的access_token
//        $access_token = RedisService::getValueWithKey('access_token');
//        if(empty($access_token)){
//            $access_token_arr = self::getAccessToken();
//            if($access_token_arr[0]){
//                $access_token = $access_token_arr[1];
//            }else{
//                return $access_token[1];
//            }
//        }
        $access_token = self::getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token[1].'&type=jsapi';
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
//        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        $ret = json_decode($data, true);
        if($ret['errcode'] == 0){
            //保存到redis
//            RedisService::setKey('ticket', $ret['ticket'], 6800);
            return [true, $ret['ticket']];
        }else{
            return [false, $ret];
        }
    }

    //获取access_token并且保存到到redis
    private static function getAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appid.'&secret='.self::$appsecret;
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
//        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        $ret = json_decode($data, true);
        if(isset($ret['errcode'])){
            return [false, $ret];
        }else{
            //保存到redis
//            RedisService::set('access_token', $ret['access_token'], 6800);
            return [true, $ret['access_token']];
        }
    }

    private static function randomkeys($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz   
               ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key = '';
        for($i=0;$i<$length;$i++)
            {
                $key .= $pattern{mt_rand(0,35)};    //生成php随机数
            }
        return $key;
    }

    public static function getOpenidByOAuthCode($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::$appid.'&secret='.self::$appsecret.'&code='.$code.'&grant_type=authorization_code';
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
//        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        $data = json_decode($data, true);
        if(isset($data['errcode'])){
            return [false, $data];
        }
        $openid = $data['openid'];
        $unionid = $data['unionid'];

        $users = XmUsers::find()->where(['openid' => $openid])->asArray()->one();
        if(empty($users)){
            //使用openid未查找到用户
            //再使用unionid查找用户信息
            $users = XmUsers::find()->where(['unionid'=>$unionid])->one();
            if (empty($users)) {
                //未查找到用户进入注册流程
                return [true, ['user'=>[
                    'id'=>null,
                    'openid' => $openid,
                    'unionid' => $unionid
                ]]];
            } else {
                //使用unnionid查找到用户，则检查是否需要绑定openid
                if (empty($users['openid'])) {
                    XmUsers::updateAll(['openid'=>$openid], ['id'=>$users['id']]);
                }
                return [true, [
                    'user'=>[
                        'id' => $users['id'],
                        'nickname' => $users['nickname'],
                        'avatar' => $users['avatar_img'] ?? 'http://xmyj.oss-cn-shanghai.aliyuncs.com/Uploads/xmsj/front/img/b-icon.png',
                        'mobile' => $users['phone'],
                        'openid' => $openid,
                        'unionid' => $data['unionid']
                    ]
                ]];
            }

        }else{
            //使用openid查找到用户则判断是否需要绑定unionid
            if (empty($users['unionid'])) {
                XmUsers::updateAll(['unionid'=>$unionid], ['id'=>$users['id']]);
            }
            return [true, [
                'user'=>[
                    'id' => $users['id'],
                    'nickname' => $users['nickname'],
                    'avatar' => $users['avatar_img'] ?? 'http://xmyj.oss-cn-shanghai.aliyuncs.com/Uploads/xmsj/front/img/b-icon.png',
                    'mobile' => $users['phone'],
                    'openid' => $openid,
                    'unionid' => $data['unionid']
                ]
            ]];
        }
    }

    //判断课程1是否购买成功
    public static function courseStatus($users_id, $course_id)
    {
        $order = XmVOrders::find()->where(['userId'=>$users_id, 'status' => 'paid', 'courseId'=>$course_id])->asArray()->one();
        $ret = array();
        if(empty($order)){
            $ret['status'] = 0;
            $ret['classesId'] = 0;
        }else{
            $ret['status'] = 1;
            $ret['class_id'] = (int)$order['classesId'];
        }
        return $ret;
    }


    //pc端支付二维码链接
    public static function pcPaymentLink($user_id, $course_id)
    {
        //微信支付二维码(扫码支付中的模式二)
        ini_set('date.timezone','Asia/Shanghai');
        //error_reporting(E_ERROR);
        require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/lib/WxPay.Api.php";
        require_once "../../vendor/WechatPay/WxpayAPI_php_v3.0.1/lib/WxPay.Data.php";

        //生成订单号用于生成订单记录
        $order_no = 'C_PC_'.date("YmdHis").rand(10000, 99999);

        //获取课程价格
        $course_price = self::getCoursePrice($course_id);

        //统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("小码世界少儿编程ScratchL1课程");
        $input->SetAttach("小码世界少儿编程ScratchL1课程");
        $input->SetOut_trade_no($order_no);
        $input->SetTotal_fee((string)$course_price);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url("alipay.xiaoma.cn/alipay.php/Alipay/wechatcoursenotifyurl");
        $input->SetTrade_type("NATIVE");
        $order_id = self::addOrder($user_id, $order_no, $course_id, $course_price, 0, 'wechat');
        $input->SetProduct_id($order_id);
        $ret = \WxPayApi::unifiedOrder($input);
        if(!empty($ret['result_code']) && $ret['result_code'] == 'SUCCESS'){
            return [true, ['code_url'=>$ret['code_url']]];
        }else{
            return [false, $ret];
        }
    }

    //生成订单价格的方法(这里暂时直接取出价格即可)
    private static function getCoursePrice($course_id)
    {
        $course = XmVCourse::find()->where(['id' => $course_id])->asArray()->one();
        return $course['price'];
    }

    //获取课程的基本信息
    public static function courseInfo($course_id)
    {
        $course = XmVCourse::find()->select('summray, title, price')->where(['id'=>$course_id])->asArray()->one();
        return $course;
    }
}