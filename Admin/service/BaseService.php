<?php

namespace Admin\service;

use Yii;
use yii\base\Controller;
use yii\db;

class BaseService extends Controller
{
	const  PER_PAGE_SIZE = 10;
	const  PAGE_PER_NUMBER=10;
	private static $_models=array();

	/**
	 * 初始化，每个Service都必须执行此方法
	 * @param string $className
	 * @return BaseService //必须添加这行注释，用于代码提示
	 * @author zhangzhicheng
	 */
	public static function model($className=__CLASS__)
	{
		if(isset(self::$_models[$className]))
			return self::$_models[$className];
		else
		{
			$model=self::$_models[$className]=new $className(null,null,[]);
// 			$model->_md=new CActiveRecordMetaData($model);
// 			$model->attachBehaviors($model->behaviors());
			return $model;
		}
	}


    public static function commonlist( $modal, $pagestart = 0,  $pageLength = 15 ,$where = null,$order=null )
    {

        if(is_array($where) && $where){
            foreach ($where as  $row){
                $modal->andWhere($row);
            }
        }
        $order = $order?$order:'id desc';
        $res['list'] = $modal->limit($pageLength)->offset($pagestart * $pageLength)->orderBy($order)->asArray()->all();
        $res['count'] = $modal->count();
        return $res;
    }
}
