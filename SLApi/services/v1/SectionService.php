<?php
namespace SLApi\services\v1;

use Yii;
use common\base\BaseService;
use common\models\orm\XmBSection;
use common\models\orm\XmBSectionMaterial;
use SLApi\services\v1\LessonService;

/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月12日 上午10:19:56 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 
class SectionService extends BaseService {
    
    public static function addSection($lesson_id, $title, $summary, $image, $sort, $content) {
        $connection = Yii::$app->db6;
        $transaction = $connection->beginTransaction();
        try {
            $section = new XmBSection();
            $section->lesson_id = $lesson_id;
            $section->add_time = time();
            $section->title = $title;
            $section->summary = $summary;
            $section->image = $image;
            $section->sort = $sort;
            $section->status = 1;
            $section->save(false);
            self::dealSectionMaterial($content, $section->attributes['id']);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $course_id = LessonService::getCourseIdByLessonId($lesson_id);
        $return = self::_fields($section->attributes);
        $return['course_id'] = $course_id;
        $return['content'] = self::getMaterialBySectionId($section->attributes['id']);       
        
        return $return;
        
    }
    
    public static function findOne($id) {
        $section = XmBSection::find()->where(['id'=> $id])->asArray()->one();
        $return = self::_fields($section);
        $materials = self::getMaterialBySectionId($id);
        $return['content'] = $materials;
        return $return;
    }
    
    public static function updateSection($data) {
        $id = $data['id'];
        $section = XmBSection::findOne(['id'=> $id]);
        $content = $data['content'] ?? [];
        unset($data['id'], $data['content']);
        $connection = Yii::$app->db6;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($data as $key=>$val) {
                $section->$key = $val;
            }
            $section->update_time = time();
            $section->save();
            if (!empty($content)) {
                self::dealSectionMaterial($content, $section->attributes['id'], false);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $return = self::_fields($section);
        $materials = self::getMaterialBySectionId($id);
        $course_id = LessonService::getCourseIdByLessonId($section->lesson_id);
        $return['course_id'] = $course_id;
        $return['content'] = $materials;
        
        return $return;
        
    }
    
    public static function deleteSection($id) {
        $model = XmBSection::findOne(['id'=> $id]);
        $model->status = 0;
        $model->save(false);
        return ['id'=>$model->id];
    }
    
    public static function getMaterialBySectionId($section_id) {
        $material_ids = XmBSectionMaterial::find()->select(['material_id'])->where(['section_id'=> $section_id])->orderBy('sort')->all();
        $materials = [];
        if (!empty($material_ids)) {
            $ids = [];
            foreach ($material_ids as $material) {
                $ids[] = $material->material_id;
            }
            $materials = MaterialService::getMaterialById($ids);
        }
        return $materials;
    }
    
    public static function getSectionByLessonId($lesson_id) {
        $sections = XmBSection::find()->select(['id', 'title'])->where(['lesson_id'=> $lesson_id, 'status'=>1])->orderBy('sort,id')->asArray()->all();
        $return = [];
        if (!empty($sections)) {
            foreach ($sections as $section) {
                $return[] = [
                    'id'=> $section['id'],
                    'title'=> $section['title'],
                ];
            }
        }
        return $return;
    }
    
    public static function _fields($obj) {
        if (is_array($obj)) {
            $return = [
                'id' => $obj['id'],
                'summary' => $obj['summary'],
                'image' => $obj['image'],
                'title' => $obj['title'],
                'lesson_id' => $obj['lesson_id'],
                'updated_time' => $obj['update_time'],
                'created_time' => $obj['add_time'],
                'status' => $obj['status'],
            ];
        } else {
            $return = [
                'id' => $obj->id,
                'summary' => $obj->summary,
                'image' => $obj->image,
                'title' => $obj->title,
                'lesson_id' => $obj->lesson_id,
                'updated_time' => $obj->update_time,
                'created_time' => $obj->add_time,
                'status' => $obj->status,
            ];
        }
        return $return; 
    }
    
    public static function dealSectionMaterial($content, $section_id, $is_add=true) {
        if (!$is_add) {
            XmBSectionMaterial::deleteAll(['section_id'=>$section_id]);
        }
        if (!empty($content)) {
            foreach ($content as $key=> $val) {
                $inData[] = [
                    'section_id' => $section_id,
                    'material_id' => $val,
                    'sort' => $key,
                ];
            }
            if ($inData) {
                $res = Yii::$app->db6->createCommand()->batchInsert(XmBSectionMaterial::tableName(),
                    ['section_id', 'material_id', 'sort'],
                    $inData)->execute();
            }
        }
        return true;
    }
    
    public static function dealLessonSection($section=[], $lesson_id, $is_add=true) {
        if (!$is_add) {
            XmBSection::updateAll(array('lesson_id'=>null),'lesson_id=:lesson_id',array(':lesson_id'=>$lesson_id));
        }
        $return = [];
        foreach ($section as $key=>$val) {
            $model = XmBSection::findOne(['id'=> $val]);
            if (isset($model)) {
                $model->lesson_id = $lesson_id;
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
 
 