<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmVUsersCourseService;

class UsersCourseServiceService extends BaseService
{
    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return CourseServiceService //必须添加这行注释，用于代码提示
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

    /**创建用户和课程服务的关系记录（在用户订单生成的同时添加一条记录进来）
     * @param $user_id
     * @param $creator
     * @param $cs_id
     * @param $order_id
     * @param int $source
     * @param string $remark
     * @return int
     */
    static public function addUsersCourseService($user_id, $cs_id, $order_id, $source = 1, $remark = '')
    {
        $usersCourseService = new XmVUsersCourseService();
        $usersCourseService->usersId = $user_id;
        $usersCourseService->courseServiceId = $cs_id;
        $usersCourseService->source = $source;
        $usersCourseService->status = 1;
        $usersCourseService->orderId = $order_id;
        $usersCourseService->remark = $remark;
        $ti = time();
        $usersCourseService->createTime = $ti;
        $usersCourseService->updateTime = $ti;
        $usersCourseService->save();
        $ret = $usersCourseService->id;
        return $ret;
    }

    //用户服务表列表哦
    static public function usersCourseService()
    {
        $list = XmVUsersCourseService::find();

        $list->asArray()->all();
        return $list;
    }
}