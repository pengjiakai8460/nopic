<?php
namespace Course\modules\admin\controllers\course;

use common\models\orm\XmVClasses;
use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\WechatService;
use Course\services\api\QiniuService;
use Course\services\api\ErrorLogsService;

class VideoController extends AdminBaseController
{
	//七牛上传视频
	public function actionUpload1()
	{
		if(empty($_FILES['file']) || !isset($_FILES['file'])) return $this->apiResult(500,'error','没有文件被上传');
		$type_str = $_FILES['file']['type'];
		$type = explode('/', $type_str);
		if($type[1] !== 'mp4') return $this->apiResult(501,'error','只能上传MP4文件');
		
		$res = QiniuService::upload1($_FILES['file']);
        if($res)
        {	
        	$addData = array();
            $addData['url'] = 'upload';
            $addData['ip'] = ErrorLogsService::getIp();
            $addData['info'] = json_encode('success');
            $addData['type'] = 'upload';
            $addData['code'] = '200';
            $addData['user_id'] = 0;
			@ErrorLogsService::addErrorLog($addData);

            return $this->apiResult(200,'success',$res);
        }
        else
        {
        	$addData = array();
            $addData['url'] = 'upload';
            $addData['ip'] = ErrorLogsService::getIp();
            $addData['info'] = json_encode('error');
            $addData['type'] = 'upload';
            $addData['code'] = '500';
            $addData['user_id'] = 0;
            @ErrorLogsService::addErrorLog($addData);
            
            return $this->apiResult(500,'error','');
        }
	}

    public function actionUpload()
    {
        if(empty($_FILES['file']) || !isset($_FILES['file'])) return $this->apiResult(500,'error','没有文件被上传');
        $type_str = $_FILES['file']['type'];
        $type = explode('/', $type_str);
        if($type[1] !== 'mp4') return $this->apiResult(501,'error','只能上传MP4文件');
        
        $res = QiniuService::upload1($_FILES['file']);
        //var_dump($res);exit();
        if($res)
        {   
            $addData = array();
            $addData['url'] = 'upload';
            $addData['ip'] = ErrorLogsService::getIp();
            $addData['info'] = json_encode('success');
            $addData['type'] = 'upload';
            $addData['code'] = '200';
            $addData['user_id'] = 0;
            @ErrorLogsService::addErrorLog($addData);

            return $this->apiResult(200,'success',$res);
        }
        else
        {
            $addData = array();
            $addData['url'] = 'upload';
            $addData['ip'] = ErrorLogsService::getIp();
            $addData['info'] = json_encode('error');
            $addData['type'] = 'upload';
            $addData['code'] = '500';
            $addData['user_id'] = 0;
            @ErrorLogsService::addErrorLog($addData);
            
            return $this->apiResult(500,'error','');
        }
    }

    public function actionGetUrl()
    {
        //$url = 'pbzotk8n1.bkt.clouddn.com/2018071916491813.m3u8';
        if(empty($_REQUEST['qiniuUrl']) || !isset($_REQUEST['qiniuUrl'])) return $this->apiResult(500,'error','qiniuUrl不得为空');
        return $this->apiResult(200,'success',QiniuService::getPrivateUrl(trim($_REQUEST['qiniuUrl'])));
    }
}