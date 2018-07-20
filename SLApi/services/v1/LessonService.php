<?php
namespace SLApi\services\v1;

use Yii;
use common\base\BaseService;
use common\models\orm\XmBLesson;
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月12日 上午10:35:44 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 
class LessonService extends BaseService {
    
    public static function getCourseIdByLessonId($lesson_id) {
        $lesson = XmBLesson::find(['course_id'])->where(['id'=> $lesson_id])->one();
        $course_id = isset($lesson) ? $lesson-> course_id : false;
        return $course_id;
    }
    
    public static function addLesson($course_id, $title, $summary, $image, $section) {
        $connection = \Yii::$app->db6;
        $transaction = $connection->beginTransaction();
        try {
            $lesson = new XmBLesson();
            $lesson->course_id = $course_id;
            $lesson->title = $title;
            $lesson->summary = $summary;
            $lesson->image = $image;
            $lesson->add_time = time();
            $lesson->status = 1;   
            $lesson->save();
            if (!empty($section)) {
                $sections = SectionService::dealLessonSection($section, $lesson->attributes['id']);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $return = $lesson->attributes;
        $return['section'] = $sections ?? [];
        return $return;
    }
    
    public static function getLessonList($page, $pageSize) {
        $lesson = XmBLesson::find();
        $lesson->where(['status'=>1]);
        $start = $pageSize * ($page - 1);
        $count = $lesson->count();
        $list = $lesson->offset($start)->limit($pageSize)->asArray()->all();
        foreach ($list as $key=>$val) {
            $list[$key]['section'] = SectionService::getSectionByLessonId($val['id']);
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
        $lesson = XmBLesson::find()->where(['id'=> $id])->asArray()->one();
        $lesson['section'] = SectionService::getSectionByLessonId($id);
        return $lesson;
    }
    
    public static function updateLesson($data) {
        $id = $data['id'];
        $connection = \Yii::$app->db6;
        $transaction = $connection->beginTransaction();
        try {
            $section = $data['section'] ?? [];
            if (!empty($section)) {
                $section = json_decode($section, true);
                $is_add = false;
                $sections = SectionService::dealLessonSection($section, $id, $is_add);
            }
            $lesson = XmBLesson::findOne(['id'=> $id]);
            unset($data['id'], $data['section']);
            foreach ($data as $key=>$val) {
                $lesson->$key = $val;
            }
            $lesson->update_time = time();
            $lesson->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return [
            'id'=> $lesson->id,
            'title' => $lesson->title,
            'course_id' => $lesson->course_id,
            'summary' => $lesson->summary,
            'image' => $lesson->image,       
            'updated_time' => $lesson->update_time,
            'created_time' => $lesson->add_time,           
            'status' => $lesson->status,
            'section' => $sections ?? SectionService::getSectionByLessonId($id),
        ];
    }
    
    public static function getLessonByCourseId($course_id) {
        $lessons = XmBLesson::find()->select(['id', 'title'])->where(['course_id'=> $course_id, 'status'=>1])->orderBy('sort,id')->asArray()->all();
        $return = [];
        if (!empty($lessons)) {
            foreach ($lessons as $lesson) {
                $return[] = [
                    'id' =>$lesson['id'],
                    'title' =>$lesson['title'],
                ];
            }
        }
        return $return;
    }
    
    public static function deleteLesson($id) {
        $lesson = XmBLesson::findOne($id);
        $lesson->status = 0;
        $lesson->update_time = time();
        $ret = $lesson->save(false);
        if (!$ret) {
            return self::error(0, "删除失败，请稍后重试！");
            
        }
        return $ret;
    }
    
    public static function checkLessonInCourse($lesson_id, $course_id) {
        $lesson = XmBLesson::findOne(['id'=> $lesson_id]);
        if (isset($lesson)) {
            return $course_id == $lesson->course_id ? TRUE : FALSE;
        }
        return FALSE;
    }
    
    public static function dealCourseLesson($lesson, $course_id, $is_add=true) {
        if (!$is_add) {
            XmBLesson::updateAll(array('course_id'=>null),'course_id=:course_id',array(':course_id'=>$course_id));
        }
        $return = [];
        foreach ($lesson as $key=>$val) {
            $model = XmBLesson::findOne(['id'=> $val]);
            if (isset($model)) {
                $model->course_id = $course_id;
                $model->sort = $key;
                $model->save(false);
            }
            if ($model->status == 1) {
                $return[] = [
                    'id'=> $model->id,
                    'title' => $model->title,
                ];
            }
        }
        return $return;
    }
}
 
 