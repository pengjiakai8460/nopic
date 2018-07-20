<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmVUserCourseGoods;


class UserCourseGoodsService extends BaseService
{
    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return ClassesService //必须添加这行注释，用于代码提示
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

    //生成课程商品与用户的记录
    static public function addUserCourseGood(array $data)
    {
        $userCourseGood = new XmVUserCourseGoods();
        foreach ($data as $key => $value) {
            $userCourseGood->$key = $value;
        }
        $userCourseGood->save();
        return $userCourseGood->id;
    }
}
