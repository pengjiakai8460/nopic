<?php
namespace Course\modules\api\controllers\course;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\RedisService;
use Course\services\api\HomeworkService;
use Course\services\api\WechatService;
use Course\services\api\QiniuService;
use Course\services\api\ComposeService;

class HomeworkController extends ApiBaseController{
    /**
     * 学生作业列表
     */
    public function actionUsershomework(){
    	if(empty($_REQUEST['limit']) || !isset($_REQUEST['limit'])) $_REQUEST['limit'] = 5;
    	if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
    		$page = intval($_REQUEST['page']);
    	}else{
    		$page = 1;
    	}
    	$data = HomeworkService::getUsersHomework($page,$_REQUEST);
    	return $this->apiResult(200,'success',$data);
    }

    //查看作业详情
    public function actionUserhomeworkinfo(){
    	if(empty($_REQUEST['id']) || !isset($_REQUEST['id'])) return $this->apiResult(500,'参数错误','');
    	$data = HomeworkService::getUserHomeworkInfo(intval($_REQUEST['id']));
    	return $this->apiResult(200,'success',$data);
    }

    //批改作业
    public function actionCorrecthomework(){
    	if(empty($_REQUEST['id']) || !isset($_REQUEST['id'])) return $this->apiResult(500,'参数错误','');
    	if(empty($_REQUEST['score']) || !isset($_REQUEST['score'])) return $this->apiResult(500,'请选择评分','');
    	if(empty($_REQUEST['comment']) || !isset($_REQUEST['comment'])) return $this->apiResult(500,'请填写评语','');
    	if(HomeworkService::correctUserHomework(intval($_REQUEST['id']),$_REQUEST)){
    		return $this->apiResult(200,'success','');
    	}else{
    		return $this->apiResult(500,'error','');
    	}
    }

    //批量
    public function actionTogglestatus(){
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id']) || !is_array($_REQUEST['id'])) return $this->apiResult(500,'参数格式错误','');
    	if(!isset($_REQUEST['status']) || empty($_REQUEST['status'])) return $this->apiResult(500,'参数错误','');
    	if(HomeworkService::toggleStatus($_REQUEST['id'],$_REQUEST['status'])){
    		return $this->apiResult(200,'success','');
    	}else{
    		return $this->apiResult(500,'error','');
    	}
    }

    //切换类型
   	public function actionChangetype(){
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) return $this->apiResult(500,'参数格式错误','');
    	if(!isset($_REQUEST['type']) || empty($_REQUEST['type'])) return $this->apiResult(500,'参数错误','');
    	//$id = json_decode($_REQUEST['id']);
    	if(HomeworkService::changeType($_REQUEST['id'],$_REQUEST['type'])){
    		return $this->apiResult(200,'success','');
    	}else{
    		return $this->apiResult(500,'error','');
    	}
    }

    /*//修改学生批改作业状态
    public function actionSaveuserhomeworkstatus(){
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) return $this->apiResult(500,'id不得为空','');
    	if(!isset($_REQUEST['status']) || empty($_REQUEST['status'])) return $this->apiResult(500,'status不得为空');
    	if(HomeworkService::changeUserHomeworkStatus($_REQUEST['id'],$_REQUEST['status'])){
    		return $this->apiResult(200,'success','');
    	}else{
    		return $this->apiResult(500,'error','');
    	}
    }

    //切换学生作业是否优秀
    public function actionSaveuserhomeworkexcellent(){
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) return $this->apiResult(500,'id不得为空','');
    	if(!isset($_REQUEST['is_excellent']) || empty($_REQUEST['is_excellent'])) return $this->apiResult(500,'is_excellent不得为空');
    	if(HomeworkService::changeUserHomeworkExcellent($_REQUEST['id'],$_REQUEST['is_excellent'])){
    		return $this->apiResult(200,'success','');
    	}else{
    		return $this->apiResult(500,'error','');
    	}
    }*/

    //点赞取消点赞
    public function actionStar()
    {
        if(!isset($_REQUEST['openid']) || empty($_REQUEST['openid'])) return $this->apiResult('500','error','openid不得为空');
        if(!isset($_REQUEST['compose_id']) || empty($_REQUEST['compose_id'])) return $this->apiResult('500','error','compose_id不得为空');
        if(!isset($_REQUEST['status'])) return $this->apiResult('500','error','status不得为空');
        $res = WechatService::composeStar($_REQUEST['openid'],$_REQUEST['compose_id'],$_REQUEST['status']);
        if($res)
        {
            return $this->apiResult(200,'success','');
        }
        else
        {
            return $this->apiResult(400,'error','');
        }
    }

    //查询作品相关信息 
    public function actionComposeinfo()
    {
        if(!isset($_REQUEST['compose_id']) || empty($_REQUEST['compose_id'])) return $this->apiResult(500,'error','compose_id不得为空');
        //if(!isset($_REQUEST['openid']) || empty($_REQUEST['openid'])) return $this->apiResult(500,'error','openid不得为空');
        $data = WechatService::getTotalInfo($_REQUEST['compose_id']);
        return $this->apiResult(200,'success',$data);
    }

    public function actionTest()
    {
        echo ComposeService::alphaID(146, false, 8, 'xmw');
    }
}