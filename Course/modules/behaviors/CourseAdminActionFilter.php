<?php
namespace Course\modules\behaviors;

use common\base\Common;
use Course\services\api\AdminService;
use yii\base\ActionFilter;

class CourseAdminActionFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        //在这里验证接口token
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

        //验证token的登录状态
        $token = \Yii::$app->request->get('token');
        if(empty($token)){
            $token = \Yii::$app->request->post('token');
        }
        if(empty($token)){
            $token = $_COOKIE['token'] ?? '';
        }
        if(!empty($token)){
            $admin = AdminService::auth($token);
            if(empty($admin)){
                Common::setSystemError(Common::ERR_NOT_LOGIN);
            }else{
                AdminService::$adminInfo = $admin;
                return parent::beforeAction($action);
            }
        }else{
            Common::setSystemError(Common::ERR_NOT_LOGIN);
        }
        return parent::beforeAction($action);
    }
}