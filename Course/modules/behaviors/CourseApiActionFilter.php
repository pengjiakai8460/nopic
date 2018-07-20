<?php
namespace Course\modules\behaviors;

use common\base\Common;
use Course\services\api\UserService;
use yii\base\ActionFilter;

class CourseApiActionFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        //过滤掉无需验证的访问
        //首先过滤模块名
        $without_login = \Yii::$app->params['without_login'];
        $module = $action->controller->module->id;
        if (in_array($module, array_keys($without_login))) {
            if (in_array('*', $without_login[$module])) { //存在'*'则该模块下所有控制器方法全部无需验证
                return parent::beforeAction($action);
            }else{
                //这里验证模块下的控制器名
                $controller = $action->controller->id;
                if(in_array($controller, array_keys($without_login[$module]))){
                    if(in_array('*', $without_login[$module][$controller])){
                        //存在'*'则该控制器下的所有方法均无需验证
                        return parent::beforeAction($action);
                    }
                    $method = $action->id;
                    if(in_array($method, $without_login[$module][$controller])){
                        return parent::beforeAction($action);
                    }
                }
            }
        }
        //获取token
        $token = \Yii::$app->request->get('token');
        if(empty($token)){
            $token = \Yii::$app->request->post('token');
        }
        if(empty($token)){
            $token = $_COOKIE['token'] ?? '';
        }
        if(!empty($token)){
            $url = env('SSO_TOKEN_URL')."?token=" . $token;
            $res = file_get_contents($url);
            $tokens = json_decode($res, true);
            if (isset($tokens['result']['info'])) {
                $info = json_decode($tokens['result']['info'], true);
                $uid = isset($info['id']) ? $info['id'] : '';
                $info['avatar_img'] = isset($info['avatar_img']) ? $info['avatar_img'] : UserService::DEFAULT_IMG;
                if (empty($uid)) {
                    Common::setSystemError(Common::ERR_NOT_LOGIN);
                } else {
                    //这里应当根据需要判断用户的一些状态
                    UserService::$userInfo = UserService::auth($token);
                }
            } else {
                //有token,但处于未登录的状态
                Common::setSystemError(Common::ERR_NOT_LOGIN);
            }
        } else {
            Common::setSystemError(Common::ERR_NOT_LOGIN);
        }
        return parent::beforeAction($action);
    }
}