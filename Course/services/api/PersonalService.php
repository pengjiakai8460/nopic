<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmCompose;
use common\models\orm\XmUsers;

class PersonalService extends BaseService
{
    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PersonalService //必须添加这行注释，用于代码提示
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

    public static function userComposeList($uid, $page = 1, $limit = 10, array $where)
    {
        $compose = XmCompose::find()->select('id, title, file, img, type, description, page_view, like_count, comment_count, add_time')->where(['user_id'=>$uid, 'status'=>1]);
        if (!empty($where['type'])) {
            if($where['type'] == 3){
                $where['type'] = 0;
            }
            $compose = $compose->andWhere(['type'=>$where['type']]);
        }
        if (!empty($where['title'])) {
            $compose = $compose->andWhere(['like', 'title', $where['title']]);
        }
        if(!empty($where['static_time'])){
//            $oneDayTime = 24*60*60;
            switch ($where['static_time']) {
                case 1:
                    $compose = $compose->andWhere(['between', 'update_time', time(), (time()-86400*7)]);
                    break;
                case 2:
                    $compose = $compose->andWhere(['between', 'update_time', time(), (time()-86400*30)]);
                    break;
                case 3:
                    $compose = $compose->andWhere(['between', 'update_time', time(), (time()-86400*365)]);
                    break;
                default:
                    break;
            }
        }
        $composeCount = clone $compose;
        $composeCount = $composeCount->count();
        $compose = $compose->offset(($page - 1)*$limit)->limit($limit);
        $compose = $compose->orderBy('id desc')->asArray()->all();
        foreach ($compose as $key => $value) {
            $imgArr = json_decode($value['img'], true);
            if (empty($imgArr[0]['name'])) {
                $img = $imgArr['name'];
            }else{
                $img = $imgArr[0]['name'];
            }
            $compose[$key]['img'] = 'http://oss.xiaoma.wang/'.$img;
            $fileArr = json_decode($value['file'], true);
            if (empty($fileArr[0]['name'])) {
                $file = $fileArr['name'];
            }else{
                $file = $fileArr[0]['name'];
            }
            $compose[$key]['file'] = 'http://oss.xiaoma.wang/'.$file;
        }
        return ['compose_list'=>$compose, 'count'=>$composeCount];
    }

    public static function usersBasicInfo($u_id)
    {
        $ret = array();
        $usersInfo = XmUsers::find()->select('follow_count, autograph')->where(['id'=>$u_id])->asArray()->one();
        $ret['follow_count'] = $usersInfo['follow_count'];//粉丝数
        $ret['autograph'] = $usersInfo['autograph'];
        $compose_count = XmCompose::find()->where(['user_id'=>$u_id ,'is_ssue'=>1, 'status'=>1])->count('id');
        $ret['compose_count'] = $compose_count;//作品总数
        $compose_like_count = XmCompose::find()->where(['user_id'=>$u_id ,'is_ssue'=>1, 'status'=>1])->sum('like_count');
        $ret['compose_like_count'] = $compose_like_count;
        return $ret;
    }

    public static function deleteCompose($compose_id, $uid)
    {
        //这里验证作品是否归属于该uid
        $compose = XmCompose::find()->where(['id'=>$compose_id])->asArray()->one();
        if($compose['user_id'] != $uid){
            return false;
        }
        return XmCompose::updateAll(['status'=>-1], ['id'=>$compose_id]);
    }
}