<?php
namespace Course\modules\api\controllers\course;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\RedisService;
use Course\services\api\VideoService;
use common\models\orm\XmVCourseVideos;
use Course\services\api\HomeworkService;
use Course\services\api\UserService;
use common\components\Aliyunoss;
use Course\services\api\QiniuService;

class VideoController extends ApiBaseController{
	
    public function actionGeturl(){
        if(empty($_REQUEST['videoId']) || !isset($_REQUEST['videoId'])) return $this->apiResult(500,'参数错误','');
    	$videoId = trim($_REQUEST['videoId']);
    	//$videoId = '56ace77098f749d38b8377af1dcd9290';
		$url = VideoService::videoPlay($videoId);
    	if($url){
    		return $this->apiResult(200,'success',$url);
    	}else{
    		return $this->apiResult(500,'error','视频不存在或已删除');
    	}
    	
    }
    //图片上传
    public function actionSaves(){
    	var_dump(VideoService::toImage());
    }
    //视频上传
    public function actionUpload(){
    	if(!empty($_REQUEST['summray'])){
    		$summray = $_REQUEST['summray'];
    	}else{
    		$summray = '';
    	}
    	if(empty($_REQUEST['title'])) return $this->apiResult(500,'请填写标题','');
    	if(empty($_REQUEST['img'])) return $this->apiResult(500,'请上传图片','');
    	//if(empty($_REQUEST['videoId'])) return $this->apiResult(500,'请上传视频','');
        if(empty($_REQUEST['qiniuUrl'])) return $this->apiResult(500,'请输入七牛上传地址','');

    	$title = trim($_REQUEST['title']);
    	//$videoId = trim($_REQUEST['videoId']);
        $videoId = isset($_REQUEST['videoId']) ? $_REQUEST['videoId'] : 1;
    	$url = $_REQUEST['img'];
        $qiniuUrl = trim($_REQUEST['qiniuUrl']);

    	$res = VideoService::uploadVideo($title,$videoId,$url,$summray,$qiniuUrl);

    	if($res){
    		return $this->apiResult(200,'保存成功',$res);
    	}else{
    		return $this->apiResult(500,'出错了，请重试','');
    	}
    }

    //视频下拉选择
    public function actionSelectvideo(){
        if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
            $page = intval($_REQUEST['page']);
        }else{
            $page = 1;
        }
    	$data = VideoService::selectVideos($page);
    
    	return $this->apiResult(200,'success',$data);
    	
    }

    //视频库
    public function actionVideolists(){
    	if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
    		$page = intval($_REQUEST['page']);
    	}else{
    		$page = 1;
    	}
    	$data = VideoService::getVideoLists($page,$_REQUEST);
    	if(!empty($data)){
    		return $this->apiResult(200,'success',$data);
    	}else{
    		return $this->apiResult(200,'暂无课程');
    	}
    }

    //用户观看模式选择 1直播模式 2点播
    public function actionCheckrecord(){
    	if(empty($_REQUEST['id']) || !isset($_REQUEST['id'])) return $this->apiResult(500,'参数错误','');
        $userinfo = UserService::$userInfo;
        $userId = $userinfo['uid'];
    	//if(empty($_REQUEST['userId']) || !isset($_REQUEST['userId'])) return $this->apiResult(500,'请登录','');
    	$data = VideoService::checkVideoRecord($_REQUEST['id'],$userId);
    	$res = array();
    	if($data){
    		$res['type'] = $data['status'] == 1 ? 1 :2;
    		if($data['status']  == 1) $res['watch_time'] = $data['watch_time'];
    	}else{
    		$res['type'] = 1;
    	}
    	return $this->apiResult(200,'success',$res);
    }

    public function actionSaverecord(){
    	if(empty($_REQUEST['videoId']) || !isset($_REQUEST['videoId'])) return $this->apiResult(500,'参数错误','');
    	//if(empty($_REQUEST['userId']) || !isset($_REQUEST['userId'])) return $this->apiResult(500,'请登录','');
        $userinfo = UserService::$userInfo;
        $_REQUEST['userId'] = $userinfo['uid'];

    	if(empty($_REQUEST['total_time']) || !isset($_REQUEST['total_time'])) return $this->apiResult(500,'请输入视频总时长','');
    	if(empty($_REQUEST['watch_time']) || !isset($_REQUEST['watch_time'])) return $this->apiResult(500,'请输入已观看时长','');
    	$data = VideoService::saveUserRecord($_REQUEST);
    	if($data){
    		return $this->apiResult(200,'success',$data);
    	}else{
    		return $this->apiResult(500,'error');
    	}
    }

    /**
     * @param $_REQUEST['url'] string base文档流
     * @return string 阿里OSS保存地址
     */
	public function actionHomeworkurl(){
		if(empty($_REQUEST['url']) || !isset($_REQUEST['url'])) return $this->apiResult(500,'请选择上传文件','');
		$data = HomeworkService::upload($_REQUEST['url']);
		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(500,'error','');
		}
	}

	//上传作业
	public function actionUploadhomework(){
		if(empty($_REQUEST['title']) || !isset($_REQUEST['title'])) return $this->apiResult(500,'请填写标题','');
		if(empty($_REQUEST['url']) || !isset($_REQUEST['url'])) return $this->apiResult(500,'请上传作业','');
		$data = HomeworkService::uploadHomework($_REQUEST);

		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(500,'error','');
		}
	}

	//查看作业
	public function actionHomeworkinfo(){
		if(empty($_REQUEST['id']) || !isset($_REQUEST['id'])) return $this->apiResult(500,'参数错误','');
		$data = HomeworkService::getHomeworkInfo($_REQUEST['id']);

		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(500,'error','');
		}
	}
	//修改视频信息
	public function actionSavevideo(){
		if(empty($_REQUEST['id']) || !isset($_REQUEST['id'])) return $this->apiResult(500,'参数错误','');
		if(empty($_REQUEST['title']) || !isset($_REQUEST['title'])) return $this->apiResult(500,'请填写标题','');
		//if(empty($_REQUEST['videoId'])) return $this->apiResult(500,'请上传视频','');
        if(empty($_REQUEST['qiniuUrl'])) return $this->apiResult(500,'请输入七牛上传地址','');
		$data = VideoService::saveVideo($_REQUEST);

		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(500,'error','');
		}
	}

	//作业选择列表
	public function actionSelecthomework(){
        if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
            $page = intval($_REQUEST['page']);
        }else{
            $page = 1;
        }
		$data = HomeworkService::selectHomework($page);
		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(200,'success','暂无数据');
		}
	}

	//作业管理列表
	public function actionHomeworklist(){
		if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
    		$page = intval($_REQUEST['page']);
    	}else{
    		$page = 1;
    	}
		$data = HomeworkService::getHomeworkLists($page,$_REQUEST);
		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(200,'success','暂无数据');
		}
	}

	//视频详情
	public function actionVideoinfo(){
		if(empty($_REQUEST['id']) || !isset($_REQUEST['id'])) return $this->apiResult(500,'参数错误','');
		$data = VideoService::getVideoInfo($_REQUEST['id']);
		if($data){
			return $this->apiResult(200,'success',$data);
		}else{
			return $this->apiResult(200,'success','暂无数据');
		}
	}


    //获取qiniu token
    public function actionGettoken()
    {
        $token = QiniuService::getToken();
        if(!empty($token))
        {
            return $this->apiResult(200,'success',$token);
        }
        else
        {
            return $this->apiResult(500,'error','');
        }
    }
}