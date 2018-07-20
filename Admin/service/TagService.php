<?php

namespace Admin\service;

use Admin\service\BaseService;
use common\models\orm\XmCTag;
use Yii;

/**
 * Content 内容管理
 */
class TagService extends BaseService{

    public static $children = false ;

    public static $parents = false;

	/**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return UsersManageService
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }



	public static function getAllLabels()
    {

        $res = ['id'=>'0','pid'=>'-1','name'=>'知识树','add_time'=>'默认','update_time'=>'默认','open'=>true];
        $labels = XmCTag::findBySql('select * from xm_c_tag where status=1')->asArray()->all();

        if($labels){
            $labels = self::formatLabelTree($labels,0);
            $res['children'] = $labels;
        }
        return $res;
    }


    public static function getchildrensByTagId($tagId)
    {

        self::$children .= self::$children !== false  ? ','. $tagId : $tagId ;

        $labels = XmCTag::find()->where(['pid'=>$tagId,'status'=>1])->asArray()->all();
        if($labels){
            foreach ($labels as $row){
                self::getchildrensByTagId($row['id']);
            }
        }
        return self::$children;
    }


    public static function getparentsNameByTagId($tagId, $implod = '/')
    {
        $labels = XmCTag::find()->where(['id'=>$tagId,'status'=>1])->asArray()->one();
        if($labels){
            self::getparentsNameByTagId($labels['pid']);
            self::$parents .=   $labels['name'].$implod ;
        }
        return self::$parents;
    }

    /**
     * 获取顶级知识点标签
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getTopTag()
    {
        $labels = XmCTag::find()->where(['pid'=>0,'status'=>1])->select('id,name')->asArray()->all();
        return $labels;
    }

    /**
     * 形成树形结构
     *
     * @param $tree
     * @param int $pid
     * @return array
     */
    public static function formatLabelTree($tree,$pid = 0)
    {
        $formatTree = [];
        foreach($tree as $row){
            $row['add_time'] = date('Y-m-d H:i:s',$row['add_time']);
            $row['update_time'] = date('Y-m-d H:i:s',$row['update_time']);
            if($row['pid'] == $pid){
                $row['children'] = self::formatLabelTree($tree,$row['id']);
                if(!$row['children']){
                    unset($row['children']);
                }
                $formatTree[] = $row;
            }
        }
        return $formatTree;
    }

    public static function showAddView($pid)
    {
        $labelInfo['pname'] = '';
        $labelInfo['pid'] = $pid;
        $labelInfo['name'] = '';
        $labelInfo['id'] = '';
        $labelInfo['sort'] = 1;
        $labelInfo['remark'] = "";
        $labelInfo['top'] = 0;
        if(is_numeric($pid) && $pid >= 0){
            if($pid == 0){
                $labelInfo['pname'] = '知识树';
                $labelInfo['pid'] = 0;
            }else{
                $PlabelInfo = XmCTag::find()->where(['id'=>$pid])->asArray()->one();
                if($PlabelInfo){
                    $labelInfo['pname'] = $PlabelInfo['name'];
                    $labelInfo['pid'] = $PlabelInfo['id'];
                    $labelInfo['top'] = $PlabelInfo['top'];
                }
            }

            return $labelInfo;
        }elseif($pid == 'topid'){
            $labelInfo['pname'] = '知识树';
            return $labelInfo;
        }
        return '';
    }


    public static function showEditView($id)
    {
        if(is_numeric($id) && $id >0){
            $labelInfo = XmCTag::find()->where(['id'=>$id])->one()->toArray();
            if($labelInfo){
                if($labelInfo['pid'] == 0){
                    $labelInfo['pname'] = '知识树';
                }else{
                    $pname = XmCTag::find()->where(['id'=>$labelInfo['pid']])->select('name')->one();
                    $labelInfo['pname'] = $pname->name;
                }
                return $labelInfo;
            }
        }
        return '';
    }

    /**
     *
     */
    public static function dosave($Data)
    {
        $resData['name'] = $Data['label_name'];
        $resData['remark'] = $Data['label_remark'];
        $resData['pid'] = ($Data['label_pid'] == 'topid'?0:$Data['label_pid']);

        $time = time();
        $isAdd = false;
        if(isset($Data['label_id']) && $Data['label_id']){
            $tagM = XmCTag::findOne($Data['label_id']);

        }else{
            $tagM = new XmCTag();
            $tagM ->add_time = $time;
            $isAdd = true;
        }

        if($tagM){
            $tagM ->pid = ($Data['label_pid'] == 'topid'?0:$Data['label_pid']);
            $tagM ->name = $Data['label_name'];
            $tagM ->remark = $Data['label_remark'];
            $tagM ->status = 1;
            $tagM ->update_time = $time;
            $tagM ->adder_id = $_SESSION['uid'];
            $tagM ->save();

            if($isAdd){
                $tagM -> top = $tagM ->pid == 0 ? $tagM->id : (XmCTag::findOne($tagM ->pid)->top);
                $tagM ->save();
            }

            $resData['id'] = $tagM->id;
            $resData['top'] = $tagM->top;
            $resData['add_time'] = $tagM->add_time;
            $resData['update_time'] = $tagM->update_time;

            return $resData;
        }else{
            return false;
        }
    }


    public static function doDelete($id)
    {

        $chirldren = XmCTag::find()->where(['pid'=>$id, "status" => 1])->count();
        if($chirldren) {
            return ['code' => 400, 'message' => '此节点为父节点 请先删除子节点'];
        }else{

            $tagM = XmCTag::findOne($id);
            $tagM->status = 0;
            $tagM->save();
            return ['code' => 200, 'message' => '删除成功'];
        }


    }
}