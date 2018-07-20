<?php
namespace Course\modules\api\controllers\pay;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\PayService;
use Course\services\api\WechatService;

class PayController extends ApiBaseController
{
    public function actionTest()
    {   
        //作业通知，参数为学生作业id $id
        $res = WechatService::getTemplates();
        var_dump($res);
    }
    public function actionTest1()
    {   
        //支付通知,参数为订单号 $sn 
        $res = WechatService::sendTemplateMessage('C_PC_2018053021052765200');
        var_dump($res);
    }
    public function actionTest2()
    {
        //开课通知,参数为$classId 
        $res = WechatService::openLessonNotice(5);
        var_dump($res);
    }
    //变更订单状态
    public function actionSaveOrderStatus()
    {
        //这里应该有验证操作
        $order_no = \Yii::$app->request->post('order_no');
        $ret = PayService::saveOrderStatus($order_no);
        return $this->apiResult(200, '成功', ['status'=>$ret]);
    }

    public function actionJsConfig()
    {
        $url = \Yii::$app->request->get('url');
        $url = urldecode($url);
        $urlArr = explode('#', $url);
        $ret = PayService::getJsConfig($urlArr[0]);
        return $this->apiResult(200, '成功', $ret);
    }

    //向微信下单并且返回前端调起支付页面需要的签名等信息
    public function actionGetSign()
    {
        $users_id = \Yii::$app->request->get('user_id');
        $openid = \Yii::$app->request->get('openid');
        $chn = \Yii::$app->request->get('chn');
        $course_id = \Yii::$app->request->get('course_id');
        if(empty($openid) && empty($users_id) && empty($course_id) && empty($chn)){
            return $this->apiResult(300, '缺少参数');
        }
        $ret = PayService::getSign($users_id, $openid, $course_id, $chn);
        $ret = json_decode($ret,true);
        return $this->apiResult(200, '成功', $ret);
    }

    public function actionOpenid()
    {
        $code = \Yii::$app->request->get('code');
        if(empty($code)){
            return $this->result(300, '参数错误');
        }
        $ret = PayService::getOpenidByOAuthCode($code);
        if(!$ret[0]){
            return $this->result(301, '微信端错误', $ret[1]);
        }
        return $this->apiResult(200, '',$ret[1]);
    }

    public function actionCourseStatus()
    {
        $users_id = \Yii::$app->request->get('user_id');
        $course_id = \Yii::$app->request->get('course_id');
        if (empty($users_id) && empty($course_id)) {
           return $this->apiResult(300, '参数错误');
        }
        $ret = PayService::courseStatus($users_id, $course_id);
        return $this->apiResult(200, '成功', $ret);
    }

    //获取课程基本信息（主要是价格）
    public function actionCourseInfo()
    {
        $course_id = \Yii::$app->request->get('course_id');
        if (empty($course_id)) {
            return $this->apiResult(300, '参数有误');
        }
        $ret = PayService::courseInfo($course_id);
        return $this->apiResult(200, '成功', $ret);
    }
}