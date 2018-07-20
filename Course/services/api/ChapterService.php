<?php
namespace Course\services\api;

use Course\modules\api\controllers\ApiBaseController;
use common\models\orm\XmVCourse;
use common\models\orm\XmVCourseChapters;
use common\base\BaseService;
use common\models\orm\XmVCourseVideos;
class ChapterService extends BaseService{
	
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
   	
   	//创建chapter
   	public static function createChapter($lessonId,$data){
   		$model = new XmVCourseChapters();
   		$model->lessonId = intval($lessonId);
   		$model->title = trim($data['title']);
        if(!empty($data['summray']) && isset($data['summray'])) $model->summray = trim($data['summray']);
        $model->createdTime = time();
        $model->save();

        return $model->id;
   	}

    //修改chapter
    public static function saveChapter($id,$data){
        $model = XmVCourseChapters::findOne(['id' => $id]);
        $model->title = $data['title'];
        if(!empty($data['summray']) && isset($data['summray'])) $model->summray = trim($data['summray']);
        if(!empty($data['videoId']) && isset($data['videoId'])){
            $model->videoId = $data['videoId'];
        }
        if(!empty($data['homeworkId']) && isset($data['homeworkId'])){
            $model->homeworkId = $data['homeworkId'];
        }
        return $model->save();
    }

    //删除lesson
    public static function delChapter($id){
        $model = XmVCourseChapters::findOne(['id' => $id]);
        $model->is_delete = 2;
        return $model->save();
    }


    //删除小节下的VIDEO
    public static function delCourseVideo($id){
        $model = XmVCourseChapters::findOne(['id' => $id]);
        $model->videoId = '';
        return $model->save();
    }

    //删除课程下的homework
    public static function delCourseHomework($id){
        $model = XmVCourseChapters::findOne(['id' => $id]);
        $model->homeworkId = '';
        return $model->save();
    }

    //删除课程下的summray
    public static function delSummray($id){
        $model = XmVCourseChapters::findOne(['id' => $id]);
        $model->summray = '';
        return $model->save();
    }
}