<?php
namespace Course\modules\api\controllers\index;

use common\models\orm\XmUsers;
use Course\modules\api\controllers\ApiBaseController;
use Course\services\alipay\AlipayService;
use Course\services\api\ErrorLogsService;
use Course\services\api\HomeworkService;
use Course\services\api\PayService;
use Course\services\api\RedisService;
use Course\services\api\StatisticService;
use Course\services\api\UserService;
use Course\services\api\WechatService;

class IndexController extends ApiBaseController
{
    public function actionIndex()
    {
//        $test = \Yii::$app->request->get('test');
        return $this->apiResult(200, 'chengle', ['test'=>111]);
    }

    //根据不同渠道统计pv
    public function actionStatistics() {
        $chn = \Yii::$app->request->get('chn');
        if (empty($chn)) {
            return false;
        }
        $day = date("Ymd");
        $key = "PV_" . $day . "_" . $chn;
        $hkey = "course_statistics";
        RedisService::hIncrBy($hkey, $key);
        StatisticService::addUrlPv((string)$chn);
        return $this->apiResult(200, '成功');
    }

    //获取对应课程的购买状态
    public function actionCourseStatus()
    {
        $token = \Yii::$app->request->get('token');
        $course_id = \Yii::$app->request->get('course_id');
        $userInfo = UserService::auth($token);
        if(empty($userInfo['uid'])){
            return $this->apiResult(300, '未登录');
        }
        if(empty($course_id)){
            return $this->apiResult(301, '参数错误');
        }
        $ret = PayService::courseStatus($userInfo['uid'], $course_id);
        return $this->apiResult(200, '接口请求成功', $ret);
    }

//    //微信网站应用登录(微信扫码登录)
//    public function actionWechatWebAppLogin()
//    {
////        $code = \Yii::$app->request->get('code');
//        $code = '011YyK4d28aCnE0mG13d2DEM4d2YyK4Q';
//        WechatService::webAppGetAccessToken($code);
//    }

    //微信登录
    public function actionWechatWebAppLogin()
    {
        $code = \Yii::$app->request->get('code');
        if (empty($code)){
            return $this->apiResult(300, '参数错误');
        }
        $data = WechatService::webAppGetAccessToken($code);
        if (!$data[0]) {

            return $this->apiResult(400, '微信端错误', $data[1]);
        }
        $unionid = $data[1]['unionid'];
        //检测数据库是否存在unionid对应的用户(这里要是不存在则进入注册流程)
        $users = WechatService::unionidIsExistence($unionid);
        if (!$users) {
            return $this->apiResult(204, '当前微信未绑定账号', ['unionid'=>$unionid]);
        }
        //使用微信登录操作
        $tokens = UserService::wechatLogin($unionid);//包含token等用户信息
        return $this->apiResult(200, '成功', $tokens);
    }

    //微信扫码登录之后的绑定或注册操作
    public function actionAddUsersUnionid()
    {
        $phone = \Yii::$app->request->post('phone');
        $phoneCode = \Yii::$app->request->post('phoneCode');
        $unionid = \Yii::$app->request->post('unionid');
        if (!($phoneCode && $phone && $unionid)) {
            return $this->apiResult(300, '参数错误');
        }
        //验证验证码
        UserService::valiPhoneCode($phone, $phoneCode);
        //进行数据操作
        $ret = WechatService::addUsersUnionid($phone, $unionid);
        if($ret[0]){
            return $this->apiResult(200, '注册成功并已自动登录', $ret[1]);
        }else{
            return $this->apiResult(400, '注册失败');
        }
    }

    //发送验证码（只应用于微信扫码登录新用户注册或者绑定行为的短信操作）
    public function actionPhoneCode()
    {
        $phone = \Yii::$app->request->get('phone');
        if (empty($phone)) {
            return $this->apiResult(300, '参数错误');
        }
        UserService::phoneCode($phone, 3);
        return $this->apiResult(200, '发送成功');
    }

    //微信端分享作品评论
    public function actionWechatShareHomeworkComment()
    {
        $usersHomework = \Yii::$app->request->get('users_homework_id');
        if (empty($usersHomework)) {
            return $this->apiResult(300, '参数错误');
        }
        $ret = HomeworkService::shareHomeworkComment($usersHomework);
        if (!$ret[0]) {
            return $this->apiResult(400, '您查看的作业尚未批改');
        }
        return $this->apiResult(200, '正常', $ret[1]);
    }

    //记录日志和统计方法相关事宜的方法
    public function actionAddLog()
    {
        $data = \Yii::$app->request->get('data');
        $data = json_decode($data, true);
        $arr = ['url', 'user_id', 'info', 'code', 'type'];
        foreach ($arr as $key => $value) {
            if (!in_array($value, array_keys($data))) {
                return $this->apiResult(301, '参数错误');
            }
        }
        switch ($data['type']) {
            case 'VISIT_PAGE':
                if (empty($data['chn'])) {
                    return $this->apiResult(301, '参数错误');
                }
                $chn = $data['chn'];
                $day = date("Ymd");
                $key = "PV_" . $day . "_" . $chn;
                $hkey = "course_statistics";
                RedisService::hIncrBy($hkey, $key);
                StatisticService::addUrlPv((string)$chn, $data['url']);
                return $this->apiResult(200, '成功');
            default:
                $addData = array();
                $addData['url'] = $data['url'];
                $addData['info'] = json_encode($data['info']);
                $addData['type'] = $data['type'];
                $addData['code'] = $data['code'];
                $addData['user_id'] = $data['user_id'] ? $data['user_id'] : 0;
                $ret = ErrorLogsService::addErrorLog($addData);
                if ($ret) {
                    return $this->apiResult(200, '成功');
                }
                return $this->apiResult(305, '程序错误');
        }
    }
}