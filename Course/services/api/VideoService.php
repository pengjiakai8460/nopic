<?php
namespace Course\services\api;

use Course\modules\api\controllers\ApiBaseController;
use common\base\BaseService;
use Addons\Aliyun\OSS\AliyunOSS;
use Addons\Aliyun\OSS\SDK2\OssClient;
use Addons\Aliyun\OSS\SDK2\Core\OssUtil;
use common\models\orm\XmVVideos;
use common\models\orm\XmVVideoRecord;

class VideoService extends BaseService{
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
    
    /**
	 * 获取视频点播播放地址
	 * @param string $VideoId
	 */
	public static function videoPlay($VideoId){
		//$data = self::httpsGet($VideoId);
        //var_dump($data);exit();
        /*if(isset($data['PlayInfoList']))
        {
            return $data['PlayInfoList']['PlayInfo'][0]['PlayURL'];
        }
        else
        {
            return false;
        }*/
        $res = XmVVideos::find()->select('qiniuUrl')->where(['id' => $VideoId])->asArray()->one();
        //var_dump($res);exit();
        if(!empty($res['qiniuUrl']))
        {
            return $res['qiniuUrl'];
        }
        else
        {
            return '';
        }
    }

    //获取视频播放时长
    public static function getVideoTime($VideoId){
    	$data = self::httpsGet($VideoId);
        if(isset($data['PlayInfoList']))
        {
            return floor($data['PlayInfoList']['PlayInfo'][0]['Duration']);
        }
        else
        {
            return false;
        }
            

    }
    //删除阿里保存的视频
    public static function deleteVideo($VideoId){
        date_default_timezone_set("UTC");
        $Timestamp =  date('c');
        $Timestamp = explode('+',$Timestamp);
        //公共参数
        $data = [
            'Version'           =>  '2017-03-21',
            'Format'            =>  'json',
            'AccessKeyId'       =>  self::$appid,
            'SignatureMethod'   =>  'HMAC-SHA1',
            'Timestamp'         =>  $Timestamp[0] . 'Z',
            'SignatureVersion'  =>  '1.0',
            'SignatureNonce'    =>  self::uuid(),
            'VideoIds'           =>  $VideoId,
            'Action'            =>  'DeleteVideo'
        ];
        
        $Signature = self::getSignature($data,self::$appsecret);
        $param = '';
        foreach($data as $key => $value){
            $param .= $key . '=' . $value . '&';
        }
        $url = 'http://vod.cn-shanghai.aliyuncs.com/';
        $param .= 'Signature=' . $Signature;
        $urls = $url . '?' .$param;
        
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$urls);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);
        $result = json_decode($result,true);
        return $result;
    }

    //通过视频videoId获取视频信息
   	public static function httpsGet($VideoId) {
        date_default_timezone_set("UTC");
        $Timestamp =  date('c');
        $Timestamp = explode('+',$Timestamp);
        //公共参数
        $data = [
            'Version'           =>  '2017-03-21',
            'Format'            =>  'json',
            //'Formats'           =>  'mp4',
            'AccessKeyId'       =>  self::$appid,
            'SignatureMethod'   =>  'HMAC-SHA1',
            'Timestamp'         =>  $Timestamp[0] . 'Z',
            'SignatureVersion'  =>  '1.0',
            'SignatureNonce'    =>  self::uuid(),
            'VideoId'           =>  $VideoId,
            'Action'            =>  'GetPlayInfo'
        ];
        
        $Signature = self::getSignature($data,self::$appsecret);
        $param = '';
        foreach($data as $key => $value){
            $param .= $key . '=' . $value . '&';
        }
        $url = 'http://vod.cn-shanghai.aliyuncs.com/';
        $param .= 'Signature=' . $Signature;
        $urls = $url . '?' .$param;

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$urls);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);
        $result = json_decode($result,true);
        return $result;
    }
    //视频播放验证签名
    public static function getSignature($data){
        ksort($data);
        $StringToSign = 'GET&' . urlencode('/') . '&';
        $str = '';
        foreach($data as  $key => $kval){
            $str .= urlencode($key) . '=' . urlencode($kval) . '&';
        }
        $str = substr($str,0,-1);
        $StringToSign .= urlencode($str);
        $Signature = urlencode(base64_encode(hash_hmac("sha1",$StringToSign,self::$appsecret . '&',true)));
        return $Signature;
    }
    
    public static function uuid($prefix = ''){
        $chars = md5(uniqid(mt_rand(), true));
        $uuid  = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);
        return $prefix . $uuid;
    }
   	
   	public static function toImage($avatar_img){
        $file = explode(';', $avatar_img);
        //文件类型
        $fileType = explode(':', $file[0])[1];
        
        //文件后缀名
        $ext = explode('/',$fileType)[1];
        
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
    //$avatar_img = json_encode($arr);
    public static function uploadAvatar($avatar_img){
        $file = explode(';', $avatar_img);
        //文件类型
        $fileType = explode(':', $file[0])[1];
        
        //文件后缀名
        $ext = explode('/',$fileType)[1];
        
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
        return $arr;
    }

   	public static function uploadVideo($title,$videoId,$url,$summray,$qiniuUrl){
   		$model = new XmVVideos();
   		
   		$model->title = $title;
   		$model->videoId = $videoId;
        $model->qiniuUrl = $qiniuUrl;//七牛地址
   		//$model->total_time = self::getVideoTime($videoId);
        $model->total_time = 0;
   		$model->src = $url;
   		$model->summray = $summray;
   		$model->createdTime = time();
   		$model->save();

   		return $model->id;
   	}

   	//修改视频信息
   	public static function saveVideo($data){
   		$model = XmVVideos::findOne(['id' => intval($data['id'])]);
   		$model->title = $data['title'];
       
   		//$model->videoId = $data['videoId'];
   		if(!empty($data['src']) && isset($data['src'])) $model->src = $data['src'];
   		if(!empty($data['summray']) && isset($data['summray'])) $model->summray = $data['summray'];
   		//$model->total_time = self::getVideoTime($data['videoId']);
   		$model->updatedTime = time();
        $model->qiniuUrl = $data['qiniuUrl'];//七牛地址
        if($model->save()){
            //替换视频,删除旧视频
            //if($model['videoId'] != $data['videoId']) @self::deleteVideo($model->videoId);
            return true;
        }else{
            return false;
        }
   	}

   	//下拉列表选择视频
   	public static function selectVideos($page){
        $total = XmVVideos::find()->count();
        $row = 5;
        $pageSize = ($page - 1) * $row;
        $total_page = ceil($total / 5);
   		$res = XmVVideos::find()->select('id,title')->limit($row)->offset($pageSize)->asArray()->all();

        $data = array();
        $data['total'] = intval($total);
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['videos'] = $res;
        $data['pageSize'] = $row;
        return $data;
   	}

   	//视频库列表
	public static function getVideoLists($page,$request){
		//$data = unserialize(RedisService::getRedis()->get('courselist' . $page));
		//if(empty($data) || !isset($data)){
			$query = XmVVideos::find()->select('id');
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

			$query_res = new \yii\db\Query();
			$query_res = XmVVideos::find()->select('*');
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
			$data['course'] = $query_res->orderBy('id desc')->limit($row)->offset($pageSize)->all();
			//$data['courses'] = XmVCourse::find()->select('id,title')->orderBy('id desc')->limit($row)->offset($pageSize)->asArray()->all();
			//RedisService::getRedis()->set('courselist' . $page, serialize($data), 3600);
		//}
		return $data;
	}

	//检测用户是否第一次观看某视频，如果是第一次，播放模式为直播模式
	public static function checkVideoRecord($id,$userId){
		$data = XmVVideoRecord::find()->where(['videoId' => $id,'userId' => $userId])->asArray()->one();
		return $data;
	}

	//记录用户观看记录
	public static function saveUserRecord($request){
		$data  = XmVVideoRecord::find()->where(['videoId' => intval($request['videoId']),'userId' => intval($request['userId'])])->asArray()->one();
		if($data){
			$model = XmVVideoRecord::findOne(['id' => $data['id']]);
		}else{
			$model = new XmVVideoRecord();
			$model->videoId = intval($request['videoId']);
			$model->userId = intval($request['userId']);
		}
		$model->watch_time = $request['watch_time'];
		$model->status = $request['total_time'] > $request['watch_time'] ? 1 : 2;
		$model->createdTime = time();
		return $model->save();
	}

	//视屏详情
	public static function getVideoInfo($id){
		return XmVVideos::findOne($id);
	}
}