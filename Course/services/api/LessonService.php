<?php
namespace Course\services\api;

use Course\modules\api\controllers\ApiBaseController;
use common\models\orm\XmVCourse;
use common\models\orm\XmVCourseLessons;
use common\base\BaseService;
class LessonService extends BaseService{
	
    private static $_models = array();
    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author zhangzhicheng
     */
    public static function model($className = __CLASS__){
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            return $model;
        }
    }
   	
   	//创建lesson
   	public static function createLesson($courseId,$data){
   		$model = new XmVCourseLessons();
   		$model->courseId = intval($courseId);
   		$model->title = $data['title'];
        //$model->startTime = $data['startTime'];
        //$model->endTime = $data['endTime'];
        $model->createdTime = time();
        $model->save();

        return $model->id;
   	}

    //修改lesson
    public static function saveLesson($id,$data){
        $model = XmVCourseLessons::findOne(['id' => $id]);
        $model->title = $data['title'];
        return $model->save();
    }

    //删除lesson
    public static function delLesson($id){
        $model = XmVCourseLessons::findOne(['id' => $id]);
        $model->is_delete = 2;
        return $model->save();
    }
}