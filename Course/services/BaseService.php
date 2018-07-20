<?php
namespace Course\services;

use common\base\BaseService as CommonBaseService;
use Course\services\traits\ServiceQuery;

class BaseService extends CommonBaseService
{
    use ServiceQuery;
    protected static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author zhangzhicheng
     */
    protected static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            return $model;
        }
    }
}