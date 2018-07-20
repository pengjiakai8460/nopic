<?php
namespace Course\modules\admin\controllers\index;

use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\AdminService;
use Course\services\api\RedisService;

class IndexController extends AdminBaseController
{
    //登录方法
    public function actionLogin()
    {
        $username = \Yii::$app->request->post('username');
        $password = \Yii::$app->request->post('password');
        if(!($username && $password)){
            return $this->apiResult(300, '参数错误');
        }
        $ret = AdminService::login((string)$username, (string)$password);
        if($ret[0]){
            $data = ['token'=>$ret[1]];
            return $this->apiResult(200, '登录成功', $data);
        }else{
            return $this->apiResult(301, '账号密码错误');
        }
    }

    //退出登录
    public function actionSignOut()
    {
        $token = \Yii::$app->request->get('token');
        if (!empty($token)) {
            $ret = AdminService::signOut($token);
            return $this->apiResult(200, '成功退出', $ret);
        }else{
            return $this->apiResult(300, '参数错误');
        }
    }


    public function actionVerifyToken()
    {
        return $this->apiResult(200, '有效');
    }
}