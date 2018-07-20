<?php
namespace Course\modules\admin\controllers\course;

use common\models\orm\XmVClasses;
use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\AdminService;
use Course\services\api\UserService;

class UsersController extends AdminBaseController
{
	/**
     * 用户列表
     */
	public function actionLists()
    {
        if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
            $page = intval($_REQUEST['page']);
        }else{
            $page = 1;
        }
        if(empty($_REQUEST['limit']) || !isset($_REQUEST['limit'])) $_REQUEST['limit'] = 10;
        $data = UserService::getUserLists($page,$_REQUEST);
        return $this->apiResult(200,'success',$data);
    }

    //添加备注
    public function actionRemark()
    {
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) return $this->apiResult(500,'id不得为空','');
    	if(!preg_match('/^\d+$/', $_REQUEST['id'])) return $this->apiResult(500,'id类型为整数','');

    	$res = UserService::remarks($_REQUEST);
    	if($res)
    	{
    		return $this->apiResult(200,'success','');
    	}
    	else
    	{
    		return $this->apiResult(500,'error','');
    	}
    }

    //切换班主任绑定状态
    public function actionTeacher()
    {
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) return $this->apiResult(500,'id不得为空','');
    	if(!preg_match('/^\d+$/', $_REQUEST['id'])) return $this->apiResult(500,'id类型为整数','');
    	if(!isset($_REQUEST['teacher_lock']) || empty($_REQUEST['teacher_lock'])) return $this->apiResult(500,'状态值不得为空','');

    	$res = UserService::bindTeacher($_REQUEST);
    	if($res)
    	{
    		return $this->apiResult(200,'success','');
    	}
    	else
    	{
    		return $this->apiResult(500,'error','');
    	}
    }

    //班级下拉列表
    public function actionClasslist()
    {
        $data = UserService::chooseClass();
        return $this->apiResult(200,'success',$data);
    }
}