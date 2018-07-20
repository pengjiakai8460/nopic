<?php
namespace SLApi\services\v1;

use common\base\BaseService;
use common\models\orm\XmBCourse;
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月12日 上午10:35:44 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 
class CourseService extends BaseService {
    public static function addcourse($title, $price, $type, $summary, $image, $lesson) {
        $connection = \Yii::$app->db6;
        $transaction = $connection->beginTransaction();
        try {
            $course = new XmBCourse();
            $course->title = $title;
            $course->price = $price;
            $course->type = $type;
            $course->summary = $summary;
            $course->images = $image;
            $course->add_time = time();
            $course->status = 1;
            $course->user_id = \Yii::$app->user->identity->id ?? 1;
            $course->save();
            if (!empty($lesson)) {
                $lessons = LessonService::dealCourseLesson($lesson, $course->attributes['id']);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $return = $course->attributes;
        $return['lesson'] = $lessons ?? [];
        return $return;
    }
    
    public static function getcourseList($page, $pageSize) {
        $course = XmBCourse::find();
        $course->where(['status'=>1]);
        $start = $pageSize * ($page - 1);
        $count = $course->count();
        $list = $course->offset($start)->limit($pageSize)->asArray()->all();
        foreach ($list as $key=>$val) {
            $list[$key]['lesson'] = LessonService::getLessonByCourseId($val['id']);
        }
        
        $page_count = count($list);
        $prev_page = $page == 1 ? null : $page - 1;
        $next_page = $count - $page * $pageSize <= 0 ? null : $page + 1;
        
        $meta = [
            'total' => $count,
            'page' => $page,
            'page_count'=> $page_count,
            'limit' => $pageSize,
            'prev_page'=> $prev_page,
            'next_page'=> $next_page,
        ];
        
        $return = ['list'=> $list, 'meta'=> $meta];
        
        return $return;
    }
    
    public static function findOne($id) {
        $course = XmBCourse::find()->where(['id'=> $id])->asArray()->one();
        $course['lesson'] = LessonService::getLessonByCourseId($course['id']);
        return $course;
    }
    
    public static function updatecourse($data) {
        $id = $data['id'];
        $connection = \Yii::$app->db6;
        $transaction = $connection->beginTransaction();
        try {
            $lesson = $data['lesson'] ?? [];
            if (!empty($lesson)) {
                $lesson = json_decode($lesson, true);
                $is_add = false;
                $lessons = LessonService::dealCourseLesson($lesson, $id, $is_add);
            }
            $course = XmBCourse::findOne(['id'=> $id]);
            unset($data['id'], $data['lesson']);
            foreach ($data as $key=>$val) {
                $course->$key = $val;
            }
            $course->update_time = time();
            $course->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return [
            'id' => $course->id,
            'title' => $course->title,
            'images' => $course->images,
            'summary' => $course->summary,
            'description' => $course->description,
            'price' => $course->price,
            'type' => $course->type,
            'lesson'=> $lessons ?? LessonService::getLessonByCourseId($course->id),
            'status' => $course->status,
            'created_time' => $course->add_time,
            'updated_time' => $course->update_time,
        ];
    }
    
    public static function deletecourse($id) {
        $course = XmBCourse::findOne($id);
        $course->status = 0;
        $course->update_time = time();
        $ret = $course->save(false);
        if (!$ret) {
            return self::error(0, "删除失败，请稍后重试！");
            
        }
        return $ret;
    }

}
 
 