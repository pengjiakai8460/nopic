<?php
namespace Course\modules\api\controllers\pay;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\PayService;
use Course\services\api\UserService;

class PayPcController extends ApiBaseController
{
    public function actionPaymentUrl()
    {
        $token = \Yii::$app->request->get('token');
        $userInfo = UserService::auth($token);
        if(empty($userInfo['uid'])){
            return $this->apiResult(300, '未登录');
        }
        $course_id = 1;
        $ret = PayService::pcPaymentLink($userInfo['uid'], $course_id);
        if($ret[0]){
            return $this->apiResult(200, '接口调用成功', $ret[1]);
        }else{
            return $this->apiResult(300, '下单行为出错', $ret[1]);
        }
    }
}