<?php
namespace Course\modules\api\controllers\course;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\RedisService;
use Course\services\api\CourseService;
use Course\services\api\LessonService;
use Course\services\api\ChapterService;
use Course\services\api\VideoService;

class CourseController extends ApiBaseController{
    public function actionTest(){
        return $this->apiResult(200, '成功',$_REQUEST);
    }
    //获取视频上传地址凭证
    public function actionAuth(){
    	$data = CourseService::getVideoAuth();
    	//var_dump(json_encode($data));exit();
    	return $this->apiResult(200, '成功',$data);
    }

    //创建课程
    public function actionCreatecourse(){
    	if(empty($_REQUEST['title']) || !isset($_REQUEST['title'])) return $this->apiResult(500,'标题不得为空','');
    	if(empty($_REQUEST['price']) || !isset($_REQUEST['price'])) return $this->apiResult(500,'价格不得为空','');
    	if(empty($_REQUEST['picture']) || !isset($_REQUEST['picture'])) return $this->apiResult(500,'请上传图片','');
    	if(empty($_REQUEST['summray']) || !isset($_REQUEST['summray'])){
    		$_REQUEST['summray'] = '';
    	}

    	if(!empty($_REQUEST)){
    		$result = CourseService::createCourse($_REQUEST);
    	
	    	if($result){
	    		return $this->apiResult(200,'保存成功',$result);
	    	}else{
	    		return $this->apiResult(500,'出错了，请重试','');
	    	}
    	}else{
    		return $this->apiResult(500,'请填写表单参数','');
    	}
    	
    }
    //修改课程信息
    public function actionSavecourse(){
    	if(!empty($_REQUEST['id']) && isset($_REQUEST['id'])){
    		if(CourseService::saveCourse($_REQUEST['id'],$_REQUEST)){
    			return $this->apiResult(200,'保存成功','');
    		}else{
    			return $this->apiResult(500,'出错了，请重试','');
    		}
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }
    //获取课程详情
    public function actionInfo(){
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		$data = CourseService::getCourseInfo(intval($_REQUEST['id']));
    		return $this->apiResult(200,'success',$data);
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    /** 
   	 * 创建lesson
     * @param int courseId
     * @param string title
     * @param int startTime
     * @param int endTime
     * @return lessonId int 
     */
    public function actionCreatelesson(){
    	if(isset($_REQUEST['courseId']) && !empty($_REQUEST['courseId'])){
    		if(empty($_REQUEST['title']) || !isset($_REQUEST['title'])) return $this->apiResult(500,'标题不得为空','');
    			$lessonId = LessonService::createLesson(intval($_REQUEST['courseId']),$_REQUEST);
	    		if($lessonId){
	    			return $this->apiResult(200,'保存成功',$lessonId);
	    		}else{
	    			return $this->apiResult(500,'出错了，请重试','');
	    		}
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    /** 
   	 * 创建chapter
     * @param int lessonId
     * @param string title
     * @return chapterId int 
     */

    public function actionCreatechapter(){
    	if(isset($_REQUEST['lessonId']) && !empty($_REQUEST['lessonId'])){
    		if(empty($_REQUEST['title']) || !isset($_REQUEST['title'])) return $this->apiResult(500,'标题不得为空','');
    		if(empty($_REQUEST['summray']) || !isset($_REQUEST['summray'])) $_REQUEST['summray'] = '';
    		$chapterId = ChapterService::createChapter($_REQUEST['lessonId'],$_REQUEST);
    		if($chapterId){
    			return $this->apiResult(200,'保存成功',$chapterId);
    		}else{
    			return $this->apiResult(500,'出错了，请重试','');
    		}
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }
    //课程下拉列表
    public function actionCourses(){
    	$data = CourseService::getCoursesList();
    	if(!empty($data)){
    		return $this->apiResult(200,'ok',$data);
    	}else{
    		return $this->apiResult(200,'暂无课程');
    	}
    }
    //课程列表
    public function actionCourselists(){
    	if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
    		$page = intval($_REQUEST['page']);
    	}else{
    		$page = 1;
    	}
    	$data = CourseService::getCourseLists($page,$_REQUEST);
    	if(!empty($data)){
    		return $this->apiResult(200,'success',$data);
    	}else{
    		return $this->apiResult(200,'暂无课程');
    	}
    }
    //切换状态 1已关闭 2已发布 3未发布
    public function actionCoursestatus(){
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		if(isset($_REQUEST['status']) && !empty($_REQUEST['status'])){
    			$res = CourseService::changeCourseStatus(intval($_REQUEST['id']),trim($_REQUEST['status']));
    			if($res){
	    			return $this->apiResult(200,'success','');
	    		}else{
	    			return $this->apiResult(500,'error','');
	    		}
    		}else{
    			return $this->apiResult(500,'error','');
    		}
    		
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }
    //删除课程 type 1可见。2隐藏
    public function actionDelcourse(){
        if(empty($_REQUEST['type']) || !isset($_REQUEST['type'])) return $this->apiResult(500,'error','');
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		$res = CourseService::delCourse(intval($_REQUEST['id']),intval($_REQUEST['type']));
    		if($res){
    			return $this->apiResult(200,'success','');
    		}else{
    			return $this->apiResult(500,'error','');
    		}
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    //修改lesson
    public function actionSavelesson(){
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		if(isset($_REQUEST['title']) && !empty($_REQUEST['title'])){
    			$res = LessonService::saveLesson(intval($_REQUEST['id']),$_REQUEST);
    			if($res){
	    			return $this->apiResult(200,'success','');
	    		}else{
	    			return $this->apiResult(500,'error','');
	    		}
    		}else{
    			return $this->apiResult(500,'标题不得为空','');
    		}
    		
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    //删除lesson
    public function actionDellesson(){
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		$res = LessonService::delLesson(intval($_REQUEST['id']));
    		if($res){
    			return $this->apiResult(200,'success','');
    		}else{
    			return $this->apiResult(500,'error','');
    		}
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    //修改chapter
    public function actionSavechapter(){
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		if(isset($_REQUEST['title']) && !empty($_REQUEST['title'])){
    			//if(empty($_REQUEST['summray']) || !isset($_REQUEST['summray'])) $_REQUEST['summray'] = 0;
    			$res = ChapterService::saveChapter(intval($_REQUEST['id']),$_REQUEST);
    			if($res){
	    			return $this->apiResult(200,'success','');
	    		}else{
	    			return $this->apiResult(500,'error','');
	    		}
    		}else{
    			return $this->apiResult(500,'标题不得为空','');
    		}
    		
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    //删除chapter
    public function actionDelchapter(){
    	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    		$res = ChapterService::delChapter(intval($_REQUEST['id']));
    		if($res){
    			return $this->apiResult(200,'success','');
    		}else{
    			return $this->apiResult(500,'error','');
    		}
    	}else{
    		return $this->apiResult(500,'参数错误','');
    	}
    }

    //排序
    public function actionSorts(){
    	if(!isset($_REQUEST['table']) || empty($_REQUEST['table'])) return $this->apiResult(500,'error:table不得为空','');
    	if(!isset($_REQUEST['id']) || empty($_REQUEST['id']) || !is_array($_REQUEST['id'])) return $this->apiResult(500,'error:参数错误','');
    	$res = CourseService::sorts(trim($_REQUEST['table']),$_REQUEST['id']);

    	if($res == 1){
    		return $this->apiResult(200,'success','');
    	}else{
    		return $this->apiResult(500,'error','');
    	}
    }

    //上传图片,返回保存地址
    public function actionUpload(){
    	if(isset($_REQUEST['picture']) || !empty($_REQUEST['picture'])){
    		$url = VideoService::toImage($_REQUEST['picture']);
    		if(!empty($url)){
    			return $this->apiResult(200,'success',$url);
    		}else{
    			return $this->apiResult(500,'error','');
    		}
    	}else{
    		return $this->apiResult(500,'请选择图片上传','');
    	}
    }

    /**
     * 删除chapter下的文件
     * @param id int chapter主键ID
     * @param type string 操作类型 video删除视频 homework删除作业 summray 删除简介
     */
    public function actionDelresources(){
        if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) return $this->apiResult(500,'error:参数错误','');
        if(!isset($_REQUEST['type']) || empty($_REQUEST['type'])) return $this->apiResult(500,'error:操作类型不得为空','');
        switch ($_REQUEST['type']) {
            case 'video':
                $data = ChapterService::delCourseVideo(intval($_REQUEST['id']));
            break;
            case 'homework':
                $data = ChapterService::delCourseHomework(intval($_REQUEST['id']));
            break;
            case 'summray':
                $data = ChapterService::delSummray(intval($_REQUEST['id']));
            break;
            default:
                # code...
            break;
        }
        if($data){
            return $this->apiResult(200,'success','');
        }else{
            return $this->apiResult(500,'error','');
        }
    }
}