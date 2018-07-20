<?php
namespace Course\modules\api\controllers\index;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\ComposeService;
use Course\services\api\UserService;

class ComposeController extends ApiBaseController
{
    //保存作品的方法
    public function actionSaveHomework()
    {
        $fileData = $_FILES;
        $json = \Yii::$app->request->post('json');
        //验证token
        $data = json_decode($json, true);
        $userInfo = UserService::auth($data['token']);
        if(empty($userInfo['uid'])){
            exit('NOLOGIN');
        }
        $str = ComposeService::saveHomework($fileData, $userInfo['uid'], $data);
        exit($str);
    }

    //保存作品新方法
    public function actionSaveHomeworknew()
    {
        $fileData = $_FILES;
        /*
         *
        //postdata = {"project_id":1,"project_kd":"strkd","project_name":"作品名称","project_url":"作品地址"}

        */
        $postData = \Yii::$app->request->post('postdata');
        $tokenKey = \Yii::$app->request->post('token_key');
        $params = \Yii::$app->request->post('params');


        //验证token
        $postData = json_decode($postData, true);
        $params = json_decode($params, true);
        $userInfo = UserService::auth($tokenKey);
        if(empty($userInfo['uid'])){
            return $this->apiResult(300, '未登录');

        }
        $ret = ComposeService::saveHomeworknew($fileData, $userInfo['uid'], ['postdata'=>$postData,'params'=>$params]);
        return $this->apiResult(200, '提交成功',$ret);
    }

    //打开作业文件
    public function actionOpenHomework()
    {
        $homework_id = \Yii::$app->request->get('homework_id');
        $class_id = \Yii::$app->request->get('class_id');
        $chapter_id = \Yii::$app->request->get('chapter_id');
        if(empty($homework_id)){
            return $this->apiResult(300, '参数错误');
        }
        $usersInfo = UserService::$userInfo;
        $ret = ComposeService::homeworkUrl($usersInfo['uid'], $homework_id, $class_id, $chapter_id);
        if (empty($ret)) {
            return $this->apiResult(301, '作业文件不存在');
        }
        return $this->apiResult(200, '成功', $ret);
    }

    //作品基本信息
    public function actionComposeInfo()
    {
        $uuid = \Yii::$app->request->get('uuid');
        if(empty($uuid)){
            return $this->apiResult(300, '缺少必要参数');
        }
        $data =  ComposeService::composeInfo($uuid);
        if(empty($data)){
            return $this->apiResult(301, '未找到作品信息');
        }
        return $this->apiResult(200, '成功', $data);
    }

    //发布作品的行为方法
    public function actionReleaseCompose()
    {
        $uuid = \Yii::$app->request->post('uuid', null);
        $title = \Yii::$app->request->post('title', null);
        $description = \Yii::$app->request->post('description', null);
        $mobile_support = \Yii::$app->request->post('mobile_support', null);
        $mobile_notice = \Yii::$app->request->post('mobile_notice', null);
        if (is_null($uuid) && is_null($title) && is_null($description) && is_null($mobile_notice) && is_null($mobile_support)) {
                        return $this->apiResult(300, '参数错误');
        }
        $data = array();
        $data['title'] = $title;
        $data['description'] = $description;
        $data['mobile_support'] = $mobile_support;
        $data['mobile_notice'] = $mobile_notice;
        $ret = ComposeService::releaseCompose($uuid, $data);
        //这里获取微信扫描查看的url地址
        $wechat_host = env('WECHAT_HOST');
//        $wechat_host = 'http://testcrm.xiaoma.wang/wechat.php';
        $compose_url = $wechat_host.'/Weiuser/wechatcompose/kd/'.$uuid;
        if ($ret) {
            return $this->result(200, '成功', ['wechat_compose_url'=>$compose_url]);
        }else{
            return $this->result(200, '记录未发生变动', ['wechat_compose_url'=>$compose_url]);
        }
    }
}