<?php
namespace Course\services\api;

use common\models\orm\XmCompose;
use common\models\orm\XmVCourseLessons;
use Course\modules\api\controllers\ApiBaseController;
use common\base\BaseService;
use Addons\Aliyun\OSS\AliyunOSS;
use Addons\Aliyun\OSS\SDK2\OssClient;
use Addons\Aliyun\OSS\SDK2\Core\OssUtil;
use common\models\orm\XmVHomework;
use common\models\orm\XmVUsersHomework;
use Course\services\api\ComposeService;
use common\models\orm\XmVClasses;
use common\models\orm\XmVCourse;
use common\models\orm\XmVCourseChapters;
use common\models\orm\XmUsers;
use Course\services\api\WechatService;

class HomeworkService extends BaseService{
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
    
    //获取参数
    public static function getKey($id){
    	$model = XmVUsersHomework::findOne(['id' => $id]);
    	return ComposeService::alphaID($model['composeId'], false, 8, 'xmw');
    }

   	public static function upload($files){
        $file = explode(';', $files);
        //文件类型
        $fileType = explode(':', $file[0])[1];
        
        //文件后缀名
        $ext = 'sb2';
        
        //文件内容本身
        $arr = explode(',', end($file));
        $fileContent = base64_decode(end($arr));
        //var_dump($fileContent);exit();
        //上传用的文件名包含后缀
        $savename = md5(time()) . '.' . $ext;

        //上传到的文件夹
        $savepath = 'Uploads/member/' . date('Y-m-d',time()) . '/';

        //上传文件的全路径包含完整文件名
        $name = $savepath . $savename;
        
        $uploadContfig = \Yii::$app->params['oss'];
        include_once '../../Addons/Aliyun/OSS/SDK2/OssClient.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Core/OssUtil.class.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Core/MimeTypes.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Http/RequestCore.class.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Http/RequestCore_Exception.class.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Core/OssException.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Http/ResponseCore.class.php';
       	include_once '../../Addons/Aliyun/OSS/SDK2/Result/PutSetDeleteResult.class.php';

        $ossClient = new OssClient($uploadContfig['AccessKeyId'], $uploadContfig['AccessKeySecret'], $uploadContfig['Endpoint']);
        $ossObj = $ossClient->putObject($uploadContfig['Bucket'], $name, $fileContent);
        $arr = array(
            'name' => $name,
            'type' => $fileType,
            'size' => $ossObj['info']['size_upload'],
            'ext'  => $ext,
            'savename' => $savename,
            'savepath' => $savepath,
            'host' => $uploadContfig['Request_Url'],
            'drive' => 'AliyunOSS'
        );

        //返回图片地址
        return $uploadContfig['Request_Url'] . $name;
   	}
   	//上传修改作业
   	public static function uploadHomework($data){
   		if(!empty($data['id']) && isset($data['id'])){
   			$model = XmVHomework::findOne(['id' => $data['id']]);
   			$model->updatedTime = time();
   		}else{
   			$model = new XmVHomework();
   			$model->createdTime = time();
   		}
   		
   		$model->title = trim($data['title']);
   		if(!empty($data['summray']) && isset($data['summray'])) $model->summray = trim($data['summray']);
   		$model->url = $_REQUEST['url'];
   		if(!empty($data['image']) && isset($data['image'])) $model->image = trim($data['image']);
   		
   		return $model->save();
   	}

   	//作业选择列表
   	public static function selectHomework($page){
   		$total = XmVHomework::find()->count();
        $row = 5;
        $pageSize = ($page - 1) * $row;
        $total_page = ceil($total / 5);
   		$res = XmVHomework::find()->select('id,title')->limit($row)->offset($pageSize)->asArray()->all();

        $data = array();
        $data['total'] = intval($total);
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['videos'] = $res;
   		$data['pageSize'] = $row;

   		return $data;
   	}

   	/** 
   	 * 批量隐藏显示
   	 * @param $id array
   	 * @param $status string 1显示 2隐藏
     * @return 
   	 */
   	public static function toggleStatus($id,$status){
   		//var_dump($id);exit();
   		$res = '';
   		foreach ($id as $k => $v) {
   			$model = XmVHomework::findOne(['id' => $v]);
   			$model->status = $status;
   			if($model->save()){
   				$res = true;
   			}
   		}
   		return $res;
   	}

   	/** 
   	 * 改变类型
   	 * @param $id array
   	 * @param $status string 1显示 2隐藏
     * @return 
   	 */
   	public static function changeType($id,$type){
   		$res = '';
   		foreach ($id as $k => $v) {
   			$model = XmVHomework::findOne(['id' => $v]);
   			$model->type = $type;
   			if($model->save()){
   				$res = true;
   			}
   		}
   		return $res;
   	}

   	/** 
   	 * 作业管理列表
     * @param $page int default 1
   	 */
   	public static function getHomeworkLists($page,$request){
   		
		$query = XmVHomework::find()->select('id');
		if(isset($request['id']) && !empty($request['id'])){
			$query->andWhere(['=','id',intval($request['id'])]);
		}
		if(isset($request['title']) && !empty($request['title'])){
			$query->andWhere(['like','title',trim($request['title'])]);
		}
		if(isset($request['time']) && !empty($request['time'])){
			$start = strtotime($request['time']) - 3600 * 24;
			$end = strtotime($request['time']) + 3600 * 24 - 1;
			$query->andWhere(['between','createdTime',$start,$end]);
		}
		

		$row = 5;//每页显示数目
		$pageSize = ($page - 1) * $row;
		$totalNums = $query->all();
		$total = $query->count(); //总记录数
		$totalPage = ceil($total / $row);//总页数

		$data = array();
		$data['totalPage'] = $totalPage;
		$data['page'] = $page;//当前页码
		$data['total'] = intval($total);
		$data['pageSize'] = $row;

		$query_res = XmVHomework::find()->select('*');
		if(isset($request['id']) && !empty($request['id'])){
			$query_res->andWhere(['=','id',intval($request['id'])]);
		}
		if(isset($request['title']) && !empty($request['title'])){
			$query_res->andWhere(['like','title',trim($request['title'])]);
		}
		if(isset($request['time']) && !empty($request['time'])){
			$start = strtotime($request['time']) - 3600 * 24;
			$end = strtotime($request['time']) + 3600 * 24 - 1;
			$query_res->andWhere(['between','createdTime',$start,$end]);
		}
		$data['homework'] = $query_res->orderBy('id desc')->limit($row)->offset($pageSize)->all();
		foreach ($data['homework'] as $k => $v) {
			$v['status'] = intval($v['status']);
			$v['type'] = intval($v['type']);
			$data['homework'][$k] = $v;
			unset($v);
		}
			
		return $data;
   	}

   	//作业详情
   	public static function getHomeworkInfo($id){
   		return XmVHomework::findOne(['id' => $id]);
   	}
   	public static function findDbname($str){
   		if(!empty($str)){
   			return explode('dbname=', $str)[1];
   		}
   	}
   	/**
     * 学生作业列表
     * @param homeworkId int $_REQUEST['homeworkId'] 作业ID
     * @param time int $_REQUEST['time'] 提交时间
     * @param title string $_REQUEST['title'] 作业名称
     * @param classId int $_REQUEST['classId'] 班级ID
     * @param userId int $_REQUEST['userId'] 用户ID
     * @param nickname string $_REQUEST['nickname'] 用户昵称
     * @param status string $_REQUEST['status'] 状态
     * @return data array
     */
   	public static function getUsersHomework($page,$request){
   		$obj = XmVUsersHomework::getDb();
   		$arr = (array)$obj;
   		$str = $arr['dsn'];
   		$db = self::findDbname($str);

   		$obj1 = XmUsers::getDb();
   		$arr1 = (array)$obj1;
   		$str1 = $arr1['dsn'];
   		$db1 = self::findDbname($str1);
   		//var_dump($db,$db1);exit();
   		$query = new \yii\db\Query();
		$query->select('count(*) as totals')
		      ->from($db . '.xm_v_users_homework as a')
		      ->join('LEFT JOIN',$db . '.xm_v_homework as b','a.homeworkId = b.id')
		      ->join('LEFT JOIN',$db . '.xm_v_classes as c','a.classId = c.id')
		      ->join('LEFT JOIN',$db1 . '.xm_users as d','a.userId = d.id')
                  ->where(['a.is_finished' => 2]);
		if(isset($request['phone']) && !empty($request['phone'])){
			$query->andWhere(['like','d.phone',intval($request['phone'])]);
		}
		if(isset($request['title']) && !empty($request['title'])){
			$query->andWhere(['like','b.title',trim($request['title'])]);
		}
		if(isset($request['updatedTime']) && !empty($request['updatedTime'])){
			$time = date('Y-m-d',$request['updatedTime']);
			$time = strtotime($time);
			$end = $time + 3600 * 24 - 1;
			$query->andWhere(['between','a.updatedTime',$time,$end]);
		}
		if(isset($request['nickname']) && !empty($request['nickname'])){
			$query->andWhere(['like','d.nickname',trim($request['nickname'])]);
		}
		if(isset($request['status']) && !empty($request['status'])){
			$query->andWhere(['=','a.status',trim($request['status'])]);
		}
		if(isset($request['className']) && !empty($request['className'])){
			$query->andWhere(['like','c.name',trim($request['className'])]);
		}
		$row = intval($request['limit']);//每页显示数目
		$pageSize = ($page - 1) * $row;
		$totalNums = $query->all();
		$total = $totalNums[0]['totals']; //总记录数
		$totalPage = ceil($total / $row);//总页数

		$data = array();
		$data['totalPage'] = $totalPage;
		$data['page'] = $page;//当前页码
		$data['total'] = intval($total);
		$data['pageSize'] = $row;

		$query_res = new \yii\db\Query();
		$query_res->select('a.id,a.homeworkId,b.title,d.id as userId,d.nickname,d.phone,c.name as className,a.updatedTime,a.status,a.is_excellent')
		      ->from($db . '.xm_v_users_homework as a')
		      ->join('LEFT JOIN',$db . '.xm_v_homework as b','a.homeworkId = b.id')
		      ->join('LEFT JOIN',$db . '.xm_v_classes as c','a.classId = c.id')
		      ->join('LEFT JOIN',$db1 . '.xm_users as d','a.userId = d.id')
              ->where(['a.is_finished' => 2]);
		/*if(isset($request['id']) && !empty($request['id'])){
			$query_res->andWhere(['=','a.id',intval($request['id'])]);
		}*/
		if(isset($request['title']) && !empty($request['title'])){
			$query_res->andWhere(['like','b.title',trim($request['title'])]);
		}
		if(isset($request['updatedTime']) && !empty($request['updatedTime'])){
			$time = date('Y-m-d',$request['updatedTime']);
			$time = strtotime($time);
			$end = $time + 3600 * 24 - 1;
			$query_res->andWhere(['between','a.updatedTime',$time,$end]);
		}
		if(isset($request['phone']) && !empty($request['phone'])){
            $query_res->andWhere(['like','d.phone',intval($request['phone'])]);
        }
		if(isset($request['nickname']) && !empty($request['nickname'])){
			$query_res->andWhere(['like','d.nickname',trim($request['nickname'])]);
		}
		if(isset($request['status']) && !empty($request['status'])){
			$query_res->andWhere(['=','a.status',trim($request['status'])]);
		}
		if(isset($request['className']) && !empty($request['className'])){
			$query_res->andWhere(['like','c.name',trim($request['className'])]);
		}
		$data['homework'] = $query_res->orderBy('id desc')->limit($row)->offset($pageSize)->all();
		return $data;
   	}
   	/**
     * 学生作业详情
     * @param id int $id 作业ID
     * @return data array
     */

   	public static function getUserHomeworkInfo($id){
   		$data = array();
   		$model = XmVUsersHomework::findOne(['id' => $id]);

   		$data['id'] = $id;
   		//用户信息
   		$userinfo = XmUsers::findOne(['id' => $model['userId']]);
   		$data['student_info']['userId'] = intval($userinfo['id']);
   		$data['student_info']['nickname'] = $userinfo['nickname'];

   		//$user_homework = XmVUsersHomework::findOne(['id' => $id]);
   		$homework_info = XmVHomework::findOne(['id' => $model['homeworkId']]);
   		$class_info = XmVClasses::findOne(['id' => $model['classId']]);
   		$course_info = XmVCourse::findOne(['id' => $class_info['courseId']]);
   		
		//章节名称
		$chapter = XmVCourseChapters::findOne(['id' => $model['chapterId']]);
 
   		$data['student_info']['className'] = $class_info['name'];
   		$data['student_info']['courseName'] = $course_info['title'];
   		$data['student_info']['homework_url'] = $model['url'];
   		$data['score'] = intval($model['score']);
   		$data['comment'] = trim($model['comment']);
   		$data['key'] = self::getKey($id);
   		$data['homework_info']['homeworkId'] = $model['homeworkId'];
   		$data['homework_info']['courseName'] = $course_info['title'];
   		$data['homework_info']['chapterName'] = $chapter['title'];
   		$data['homework_info']['old_url'] = $homework_info['url'];

		return $data;
   	}

   	/**
     * 批改作业
     * @return int 
   	 */
   	public static function correctUserHomework($id,$data){
   		$model = XmVUsersHomework::findOne(['id' => $id]);
   		$model->score = $data['score'];
   		$model->comment = $data['comment'];
   		$model->status = 2;
   		$model->is_excellent = $data['score'] >= 8 ? 1 : 2;
      $model->updatedTime = time();

   		if($model->save())
        {
            @WechatService::saveHomework($id);
            return true;
        }
        else
        {
            return false;
        }
   	}

   	/*//学生作业状态变更
	public static function changeUserHomeworkStatus($id,$status){
		$model = XmVUsersHomework::findOne(['id' => $id]);
		$model->status = $status;
		return $model->save();
	}

	//学生作业优秀变更
	public static function changeUserHomeworkExcellent($id,$is_excellent){
		$model = XmVUsersHomework::findOne(['id' => $id]);
		$model->is_excellent = $is_excellent;
		return $model->save();
	}*/

   	//分享作业评论
    public static function shareHomeworkComment($users_homework_id)
    {
        $usersHomework = XmVUsersHomework::find()->where(['id'=>$users_homework_id, 'status'=>2])->asArray()->one();
        $ret = [];
        if (!empty($usersHomework)) {
            //查询对应的课的名称
            $courseChapters = XmVCourseChapters::find()->select('lessonId')->where(['id'=>$usersHomework['chapterId']])->asArray()->one();
            $courseLessons = XmVCourseLessons::find()->select('title')->where(['id'=>$courseChapters['lessonId']])->asArray()->one();
            $ret['title'] = $courseLessons['title'];
            //查询用户头像地址
            $users = XmUsers::find()->select('avatar_img')->where(['id'=>$usersHomework['userId']])->asArray()->one();
            $avatar_img = json_decode($users['avatar_img'], true);
            $ret['avatar_img'] = !empty($avatar_img) ? 'http://oss.xiaoma.wang/'.$avatar_img['name'] : env('DEFAULT_IMG');
            //查询作品的截图地址
            $compose = XmCompose::find()->select('img')->where(['id'=>$usersHomework['composeId']])->asArray()->one();
            $img = json_decode($compose['img'], true);
            if (!empty($img['name'])) {
                $ret['compose_img'] = 'http://oss.xiaoma.wang/'.$img['name'];
            }else{
                $ret['compose_img'] = 'http://oss.xiaoma.wang/'.$img[0]['name'];
            }
            //作业的评分
            $ret['homework_comment_score'] = $usersHomework['score'];
            //作业的评论
            $ret['homework_comment'] = $usersHomework['comment'];
            return [true, $ret];
        }
        return [false];
    }

    //删除学生的在班级中的作业记录的方法
    public static function delUsersHomework($usersHomework_id)
    {
        $usersHomework = XmVUsersHomework::updateAll(['is_delete'=>2], ['id'=>$usersHomework_id]);
        if ($usersHomework == 1) {
            return true;
        }
        return false;
    }
}