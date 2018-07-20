<?php
namespace Course\services\api;

use common\models\orm\XmVClasses;
use common\models\orm\XmVClassesLesson;
use common\models\orm\XmVOrders;
use Course\modules\api\controllers\ApiBaseController;
require_once '../../vendor/aliupload/aliyun-php-sdk-core/Config.php';
use vod\Request\V20170321 as vod;
use common\models\orm\XmVCourse;
use common\models\orm\XmVCourseLessons;
use common\models\orm\XmVCourseChapters;
use common\base\BaseService;
use Course\services\api\VideoService;
use common\models\orm\XmVHomework;
use common\models\orm\XmVVideos;

class CourseService extends BaseService{
	//视频点播参数
    private static $appid = 'LTAIUsUkhIa68cs9';
    private static $appsecret = 'JHH82zsFC3xuJb8ZsQc652C1PLoiEg';

    private static $_models = array();
    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author zhangzhicheng
     */
    public static function model($className = __CLASS__){
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            return $model;
        }
    }
    //获取视频上传凭证地址
    public static function getVideoAuth(){
    	return self::create_upload_video(self::getClient(), 'cn-shanghai');
    }

	public static function create_upload_video($client, $regionId) {
   		$request = new vod\CreateUploadVideoRequest();
   		//视频源文件标题(必选)
   		$request->setTitle("视频标题");
   		//视频源文件名称，必须包含扩展名(必选)
   		$request->setFileName("文件名称.mov");
   		//视频源文件字节数(可选)
   		$request->setFileSize(0);
   		//视频源文件描述(可选)
   		$request->setDescription("视频描述");
   		//自定义视频封面URL地址(可选)
   		$request->setCoverURL("http://cover.sample.com/sample.jpg");
   		//上传所在区域IP地址(可选)
   		$request->setIP("127.0.0.1");
   		//视频标签，多个用逗号分隔(可选)
   		$request->setTags("标签1,标签2");
   		//视频分类ID(可选)
   		$request->setCateId(0);
   		$response = $client->getAcsResponse($request);
   		return $response;
	}

	public static function getClient(){
		include_once '../../vendor/aliupload/aliyun-php-sdk-core/Profile/DefaultProfile.php';
		$regionId = 'cn-shanghai';
		$profile = \DefaultProfile::getProfile($regionId, self::$appid, self::$appsecret);
		$client = new \DefaultAcsClient($profile);
		return $client;
	}

	//创建课程
	public static function createCourse($data){
		$model = new XmVCourse();

		$model->title = trim($data['title']);
		$model->price = $data['price'];
		$model->summray = $data['summray'];
		$model->picture = $data['picture'];
		$model->createdTime = time();
		$model->save();

		return $model->id;
	}

	//课程详情
	public static function getCourseInfo($courseId){
		$data = array();
		$data = XmVCourse::find()->select('id,title,summray,price,picture as image')->where(['id' => $courseId,'is_delete' => 1])->asArray()->one();
		if(!empty($data)){
			$data['lessons'] = XmVCourseLessons::find()->select('id,title,courseId as course_id')->where(['courseId' => $courseId,'is_delete' => 1])->asArray()->all();
		}
        //sections
        if(!empty($data['lessons'])){
        	foreach($data['lessons'] as $k => $v){
        		if(!empty($v)){
        			$v['sections'] = XmVCourseChapters::find()->select('id,title,lessonId as lesson_id,summray,videoId,homeworkId')->where(['lessonId' => $v['id'],'is_delete' => 1])->asArray()->all();
        			if(!empty($v['sections'])){
        				foreach ($v['sections'] as $k1 => $v1) {
        					$videos = XmVVideos::find()
        									->select('id,title,videoId,src AS image,total_time')
        									->where(['id' => $v1['videoId']])
        									->one();
        					$homework = XmVHomework::find()->select('id,title,image,url,summray')->where(['id' => $v1['homeworkId']])->asArray()->one();
        					$v1['video'] = $videos;
        					$v1['homework'] = $homework;
        					unset($v1['videoId']);
        					unset($v1['homeworkId']);
        					$v['sections'][$k1] = $v1;
        				}
        			}
        			$data['lessons'][$k] = $v;
        		}
	        }
        }
		return $data;
	}

	//修改课程
	public static function saveCourse($courseId,$data){
		$model = XmVCourse::findOne(['id' => $courseId]);
		$model->title = trim($data['title']);
		$model->price = $data['price'];
		$model->summray = $data['summray'];
		$model->picture = $data['picture'];
		$model->createdTime = time();

		return $model->save();
	}

	//课程下拉列表选择
	public static function getCoursesList(){
		return XmVCourse::find()->select('id,title')->where(['status' => 'published'])->asArray()->all();
	}

	//课程列表
	public static function getCourseLists($page,$request){
		
		$query = XmVCourse::find()->select('id')->where(['is_delete'=>1]);
		
		if(isset($request['id']) && !empty($request['id'])){
			$query->andWhere(['=','id',intval($request['id'])]);
		}
		if(isset($request['title']) && !empty($request['title'])){
			$query->andWhere(['like','title',trim($request['title'])]);
		}
		if(isset($request['create_time']) && !empty($request['create_time'])){
			$time = date('Y-m-d',$request['create_time']);
			$time = strtotime($time);
			$end = $time + 3600 * 24 - 1;
			$query->andWhere(['between','createdTime',$time,$end]);
		}
		if(isset($request['status'])){
			if(!empty($request['status'])) $query->andWhere(['=','status',intval($request['status'])]);
		}

		$row = 5;//每页显示数目
		$pageSize = ($page - 1) * $row;
		$totalNums = $query->all();
		$total = $query->count(); //总记录数
		$totalPage = ceil($total / $row);//总页数

		$data = array();
		$data['totalPage'] = $totalPage;
		$data['page'] = $page;//当前页码
		$data['nums'] = intval($total);
		$data['pageSize'] = $row;

		$query_res = XmVCourse::find()->select('id,title,summray,price,picture,status,createdTime,creator,is_delete')->where(['is_delete'=>1]);
		if(isset($request['id']) && !empty($request['id'])){
			$query_res->andWhere(['=','id',intval($request['id'])]);
		}
		if(isset($request['title']) && !empty($request['title'])){
			$query_res->andWhere(['like','title',trim($request['title'])]);
		}
		if(isset($request['create_time']) && !empty($request['create_time'])){
			$time = date('Y-m-d',$request['create_time']);
			$time = strtotime($time);
			$end = $time + 3600 * 24 - 1;
			$query_res->andWhere(['between','createdTime',$time,$end]);
		}
		if(isset($request['status'])){
			if(!empty($request['status'])) $query_res->andWhere(['=','status',intval($request['status'])]);
		}
		$data['course'] = $query_res->orderBy('id desc')->limit($row)->offset($pageSize)->all();
		foreach ($data['course'] as $k => $v) {
			if($v['status'] == 'closed'){
				$v['status'] = 1;
			}elseif($v['status'] == 'published'){
				$v['status'] = 2;
			}elseif($v['status'] == 'unpublished'){
				$v['status'] = 3;
			}
			$data['course'][$k] = $v;
		}
		
		return $data;
	}

	//删除课程
	public static function delCourse($id,$type){
		$model = XmVCourse::findOne(['id' => $id]);
		$model->is_delete = $type;
		return $model->save();
	}

	//课程状态变更
	public static function changeCourseStatus($id,$status){
		$model = XmVCourse::findOne(['id' => $id]);
		$model->status = $status;
		return $model->save();
	}

	/**
     * 排序
     * @param array $id
     * @param string $model course课程 chapter小节 lesson章节
     * @return status int 1成功2失败
	 */

	public static function sorts($table,$id){
		switch ($table) {
			case 'course':
				$res = XmVCourse::findAll($id);
				break;
			case 'lesson':
				$res = XmVCourseLessons::findAll($id);
				break;
			case 'chapter':
				$res = XmVCourseChapters::findAll($id);
				break;
			default:
				# code...
				break;
		}
		$status = '';
		for ($i = 0;$i < count($res);$i ++ ){
			for($j = 0;$j < count($id); $j ++){
				switch ($table) {
					case 'course':
						$model = XmVCourse::findOne($res[$i]['id']);
						break;
					case 'lesson':
						$model = XmVCourseLessons::findOne($res[$i]['id']);
						break;
					case 'chapter':
						$model = XmVCourseChapters::findOne($res[$i]['id']);
						break;
					default:
						# code...
						break;
				}

				//修改seq
				if($model['id'] == $id[$j]){
					$model->seq = $j + 1;
					if($model->save()){
						$status = 1;
					}else{
						$status = 2;
					}
				}
			}
		}
		return $status;
	}
	
	//用户课程列表
	public static function usersCourseList($users_id)
	{
		//首先查找报名成功的课程信息
		$orders = XmVOrders::find()->select('id, courseId, classesId')->where(['userId'=>$users_id, 'status'=>'paid'])->asArray()->all();
		$usersCourseList = array();
		foreach ($orders as $key => $value) {
		    $usersCourseList[$key]['course_id'] = $value['courseId'];
		    $usersCourseList[$key]['course_status'] = 1;
		    $usersCourseList[$key]['classes_id'] = $value['classesId'];
		    if ($value['classesId'] == 0) {
                $usersCourseList[$key]['course_status'] = 0;
            }
		}
		$ret = array();//接口所需数据
		if (!empty($usersCourseList)) {
		    foreach ($usersCourseList as $key => $value) {
                $courseInfo = XmVCourse::findOne($value['course_id']);//课程名称等信息
                $ret[$key]['course_title'] = $courseInfo['title'];
                $ret[$key]['course_picture'] = $courseInfo['picture'];
                $ret[$key]['course_status'] = $value['course_status'];
                $ret[$key]['course_id'] = $value['course_id'];
                //查看对应课程的课总数
                $courseLessCount = XmVCourseLessons::find()->where(['courseId'=>$value['course_id'], 'is_delete' => 1])->asArray()->count();
				$ret[$key]['course_less_count'] = $courseLessCount;
                $ret[$key]['class_less_count'] = 0;
                $ret[$key]['last_lesson_title'] = '';
                $ret[$key]['class_id'] = 0;
				$ret[$key]['class_open_time'] = 0;
				$ret[$key]['class_end_time'] = 0;
				
		        if ($value['course_status'] == 1) {
					//班级基本信息
					$classes = XmVClasses::find()->where(['id' => $value['classes_id']])->asArray()->one();
					$ret[$key]['class_open_time'] = $classes['openTime'];
					$ret[$key]['class_end_time'] = $classes['closeTime'];
                    //查看班级内对应的课程解锁章节数
                    $classLessCount = XmVClassesLesson::find()->where(['classesId'=>$value['classes_id']])->asArray()->count();
//                    $ret['speed_progress'] = $classLessCount/$courseLessCount;
                    $ret[$key]['class_less_count'] = $classLessCount;
                    //查询最后开课的课名
                    $classesLesson = XmVClassesLesson::find()->select('id, lessonId')->where(['classesId'=>$value['classes_id']])->asArray()->orderBy('id desc')->one();
				
                    $courseLesson = XmVCourseLessons::find()->where(['id'=>$classesLesson['lessonId']])->asArray()->one();
                    $ret[$key]['last_lesson_title'] = $courseLesson['title'];
                    $classes = XmVClasses::findOne($value['classes_id']);
                    $ret[$key]['class_id'] = $classes['id'];
                }
            }
        }
        return $ret;
	}
}