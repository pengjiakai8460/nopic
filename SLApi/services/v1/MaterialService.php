<?php
namespace SLApi\services\v1;

use common\base\BaseService;
use common\models\orm\XmBCourse;
use common\models\orm\XmBMaterial;
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月12日 上午10:35:44 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 
class MaterialService extends BaseService {
    
    public static function getMaterialById($id) {
        $fields = [
            'id',
            'attr',
            'value',
            'type',
        ];
        
        foreach ($id as $key=>$val) {
            $material[$key] = XmBMaterial::find()->select($fields)->where(['id'=> $val])->asArray()->all();
        }
        return $material;
    }
    
    public static function addMaterial($title, $type, $value, $attr) {
        $material = new XmBMaterial();
        $material->title = $title;
        $material->type = $type;
        $material->value = $value;
        $material->add_time = time();
        $material->status = 1;
        if (!empty($attr)) {
            $material->attr = json_encode(json_decode($attr, true));
        }  
        $material->save();
        $return = $material->attributes;

        return $return;
    }
    
    public static function getMaterialList($page, $pageSize) {
        $material = XmBMaterial::find();        
        $material->where(['status'=>1]);       
        $start = $pageSize * ($page - 1);
        $count = $material->count();     
        $list = $material->offset($start)->limit($pageSize)->all();        
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
        $material = XmBMaterial::find()->where(['id'=> $id])->asArray()->one();
        return $material;
    }
    
    public static function updateMaterial($data) {
        $id = $data['id'];
        $material = XmBMaterial::findOne(['id'=> $id]);
        unset($data['id']);
        if (empty($data['attr'])) {
            unset($data['attr']);
        }
        foreach ($data as $key=>$val) {
            $material->$key = $val;
        }
        $material->update_time = time();
        $material->save();
        return $material;
    }
    
    public static function deleteMaterial($id) {
        $material = XmBMaterial::findOne($id);
        $material->status = 0;
        $material->update_time = time();
        $ret = $material->save(false);
        if (!$ret) {
            return self::error(0, "删除失败，请稍后重试！");
            
        }
        return $ret;
    }

}
 
 