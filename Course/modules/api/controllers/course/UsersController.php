<?php
namespace Course\modules\api\controllers\course;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\RedisService;
use Course\services\api\UserService;
use Course\services\api\WechatService;
use Course\services\api\VideoService;
use Course\services\api\YzService;

class UsersController extends ApiBaseController
{
	public function actionUserinfo()
    {
    	$info = UserService::$userInfo;
        $id = $info['uid'];
        $data = UserService::getUserinfo($id);
       	return $this->apiResult(200,'success',$data);
    }

    public function actionAreas()
    {
    	$code = (empty($_REQUEST['code']) || !isset($_REQUEST['code'])) ? 0 : $_REQUEST['code'];
    	$data = UserService::getAreas(intval($code));
    	return $this->apiResult(200,'success',$data);
    }

    /**
     * 保存用户信息
     * @param userId test 3272
     */

    public function actionSave()
    {	
    	if(empty($_REQUEST['nickname']) || !isset($_REQUEST['nickname'])) return $this->apiResult(500,'error','昵称不得为空');
    	$info = UserService::$userInfo;
        $id = $info['uid'];
    	$data = UserService::saveUserinfo($id,$_REQUEST);
    	if(isset($data['code']) && $data['code'] == 2)
    	{
    		return $this->apiResult(500,'error',$data['msg']);
    	}
    	else
    	{
    		return $this->apiResult(200,'success','');
    	}
    }

    //搜索学校
    public function actionSchools()
    {
    	if(isset($_REQUEST['name']))
    	{
    		$data = UserService::getSchools($_REQUEST['name']);
    		return $this->apiResult(200,'success',$data);
    	}
    }

    //更换手机第一部
    public function actionCheck()
    {
    	$info = UserService::$userInfo;
        $id = $info['uid'];
    	if(empty($_REQUEST['phone']) || !isset($_REQUEST['phone'])) return $this->apiResult(500,'error','手机号码不得为空');
    	if(!preg_match("/^1[34578]\d{9}$/",$_REQUEST['phone'])) return $this->apiResult(500,'error','手机号码格式错误');
    	$data = UserService::checkMobile($id,$_REQUEST['phone']);
 		
    	if(empty($data))
    	{
    		return $this->apiResult(500,'error','手机号用户不匹配');
    	}
    	else
    	{
    		return $this->apiResult(200,'success',$data);
    	}
    }

    //更换绑定手机
    public function actionReplacephone()
    {
    	$info = UserService::$userInfo;
        $id = $info['uid'];
    	if(empty($_REQUEST['phone']) || !isset($_REQUEST['phone'])) return $this->apiResult(500,'error','手机号码不得为空');
    	if(!preg_match("/^1[34578]\d{9}$/",$_REQUEST['phone'])) return $this->apiResult(500,'error','手机号码格式错误');
    	if(empty($_REQUEST['code']) || !isset($_REQUEST['code'])) return $this->apiResult(500,'error','验证码不得为空');
    	if(!preg_match("/^\d+$/",$_REQUEST['code'])) return $this->apiResult(500,'error','验证码只能是数字');
    	$data = UserService::replacePhone($id,$_REQUEST['phone'],$_REQUEST['code']);
 		//13120245146
    	if(!$data)
    	{
    		return $this->apiResult(500,'error','修改失败');
    	}
    	else
    	{
    		return $this->apiResult(200,'success','');
    	}
    }

    //忘记密码
    public function actionForget()
    {
    	$info = UserService::$userInfo;
        $id = $info['uid'];
    	if(empty($_REQUEST['old_password']) || !isset($_REQUEST['old_password'])) 
    	if(empty($_REQUEST['new_password']) || !isset($_REQUEST['new_password'])) return $this->apiResult(500,'error','请输入新密码');
    	if(empty($_REQUEST['new_password1']) || !isset($_REQUEST['new_password1'])) return $this->apiResult(500,'error','请再次输入新密码');
    	if(!preg_match('/^\w{6,12}$/', $_REQUEST['old_password'])) return $this->apiResult(500,'error','旧密码长度为6-12，仅包含数字字母');
        if(!preg_match('/^\w{6,12}$/', $_REQUEST['new_password'])) return $this->apiResult(400,'error','新密码长度为6-12，仅包含数字字母');
        if($_REQUEST['new_password'] !== $_REQUEST['new_password1']) return $this->apiResult(500,'error','两次密码输入不一致');
        //e10adc3949ba59abbe56e057f20f883e e10adc3949ba59abbe56e057f20f883e fe743d8d97aa7dfc6c93ccdc2e749513
    	$data = UserService::forgetPassword($id,$_REQUEST);
    	if($data['code'] == 2)
    	{
    		return $this->apiResult(200,'success','');
    	}
    	else
    	{
    		return $this->apiResult(500,'error',$data['msg']);
    	}
    }

    //用户绑定状态
    public function actionBind()
    {
    	$info = UserService::$userInfo;
        $id = $info['uid'];
        $data = UserService::getUserBindInfo($id);
        if($data)
        {
            return $this->apiResult(200,'success',$data);
        }
        else
        {
            return $this->apiResult(500,'error','');
        }
        
    }

    //用户绑定微信
    public function actionCallback()
    {
    	/*$info = UserService::$userInfo;
        $id = $info['uid'];*/
        if(empty($_REQUEST['state']) || !isset($_REQUEST['state'])) return $this->apiResult(400,'error','state不得为空');
        if(empty($_REQUEST['code']) || !isset($_REQUEST['code'])) return $this->apiResult(402,'error','code不得为空');
        
        $res = WechatService::userBindOpenid($_REQUEST['code'],intval($_REQUEST['state']));
        if($res)
        {
            return $this->apiResult(200,'success',$res);
        }
        else
        {
            return $this->apiResult(500,'error','操作失败');
        }
        
    }

    //获取扫码地址
    public function actionUrl()
    {
        $info = UserService::$userInfo;
        $id = $info['uid'];
        $callback = \Yii::$app->request->hostInfo . '/api/course/users/callback?id=' . $id;
        return $this->apiResult(200,'success',$callback);
    }

    //检测用户是否绑定成功
    public function actionCheckbind()
    {
        $info = UserService::$userInfo;
        $id = $info['uid'];
        $res = UserService::checkUserBindWechat($id);
        return $this->apiResult(200,'success',$res);
    }

    public function actionUpload()
    {	
    	if(empty($_REQUEST['picture']) || !isset($_REQUEST['picture'])) return $this->apiResult(500,'请上传图片','');
    	$arr = VideoService::uploadAvatar($_REQUEST['picture']);
    	if($arr)
    	{
    		return $this->apiResult(200,'success',$arr);
    	}
    	else
    	{
    		return $this->apiResult(500,'error','上传失败');
    	}
    }
}