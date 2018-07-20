<?php
namespace Course\services\api;

use common\models\orm\XmVClassesLesson;
use common\models\orm\XmVCourseLessons;
use Course\modules\api\controllers\ApiBaseController;
use common\base\BaseService;
use common\models\orm\XmVOrders;
use common\models\orm\XmUsers;
use common\models\orm\XmVCourse;
use common\models\orm\XmVClasses; 
use common\models\orm\XmVClassesUsers;
use common\models\orm\XmVUsersHomework;
use common\models\orm\XmWechatUser;
use common\models\orm\XmWechatComposeZan;
use common\models\orm\XmCompose;
use Course\services\api\ComposeService;

class WechatService extends BaseService
{
	//小码世界教学服务公众号微信参数
    private static $appid = 'wx60ab09a315faea22';
    private static $appsecret = '57465a0c044eff06f4f73aab04ae3b5e';
    private static $grant_type = 'client_credential';

    //小码世界开放平台下网站应用的微信参数
    private static $web_app_id = 'wxf657e3fb3dbb846f';
    private static $web_app_secret = '5f328b1d37f83477f9c7954d34437345';
    private static $web_app_access_token = 'https://api.weixin.qq.com/sns/oauth2/access_token';//通过code获取access_token

    private static $_models = array();
    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
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

    /**
     * 换取token
     */

    public static function getToken()
    {
    	$params = array('grant_type' => self::$grant_type,'appid' => self::$appid,'secret' => self::$appsecret);
    	$url = 'https://api.weixin.qq.com/cgi-bin/token';
    	$res = json_decode(self::doRequest($url,$params));
    	return $res->access_token;
    }

    /**
     * 发送请求
     * @param $url string 请求地址
     * @param $data array 请求参数
     */
    public static function doRequest($url,$data)
    {
    	$curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        if(!empty($data))
        {
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * 获取模板列表
     * @return object
     */
    public static function getTemplates()
    {
    	$access_token = self::getToken();
    	$data = array('access_token' => $access_token);
    	$url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template';
    	return json_decode(self::doRequest($url,$data));
    }

    /** 
     * 发送模板信息，订单支付
     * @param $openId string 
     * @param $sn string 订单号
     */
    public static function sendTemplateMessage($sn)
    {
    	$order = XmVOrders::findOne(['orderSn' => $sn]);
    	$course = XmVCourse::find()->select('title')->where(['id' => $order->courseId])->one();
    	$users = XmUsers::find()->select('phone,openid')->where(['id' => $order->userId])->one();
    	    
    	//获取token
        $token = self::getToken();
        //设置url
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token;
        //设置发送的消息
        $message = [
            'touser' => $users->openid,
            'template_id' => 'SViWnmcX3dW6wpKXTx0S0ZTzn19K9X7D3wO8x40u818',
            //'url' => $cz_url,
            'data' => [
                'first' => ['value' => '恭喜你购买成功！','color' => '#173177'],
                'keyword1' => ['value' => $sn,'color' => '#173177'],
                'keyword2' => ['value' => $course->title,'color' => '#173177'],
                'keyword3' => ['value' => $order->price / 100,'color' => '#173177'],
                'keyword4' => ['value' => $users->phone,'color' => '#173177'],
                'keyword5' => ['value' => date('Y-m-d H:i:s',time()),'color' => '#173177'],
                'remark' => ['value'=>'欢迎再次购买','color' => '#173177']
            ]
        ];

        $data = json_encode($message);

        //发送
        $res = self::doRequest($url,$data);
        return $res;
    }

    /**
     * 开课通知
     * @param $classId int
     */

    public static function openLessonNotice($classesLesson_id)
    {
        //班级开课记录表信息
        $classesLesson = XmVClassesLesson::find()->where(['id'=>$classesLesson_id])->asArray()->one();
        //班级id
        $classId = $classesLesson['classesId'];
        //获取章节名称
        $courseLesson = XmVCourseLessons::find()->where(['id'=>$classesLesson['lessonId']])->asArray()->one();
        $lessonName = $courseLesson['title'];//章节名称
        $openLessonTime = $classesLesson['createTime'];//开课时间
    	$users = XmVClassesUsers::find()->select('usersId')->where(['classId' => $classId])->asArray()->all();
    	//var_dump($users);exit();

    	if(!empty($users))
    	{	
    		$token = self::getToken();
    		foreach ($users as $k => $v) 
    		{
		        //设置url
		        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token;
		        //设置发送的消息
		        $userinfo = XmUsers::find()->select('*')->where(['id' => intval($v['usersId'])])->asArray()->one();
		        if(!empty($userinfo['openid']))
		        {
		        	$message = [
			            'touser' => $userinfo['openid'],
			            'template_id' => 'Zzo1kAD3AErotLCqUyiMfPNWxa0SNBy2m8-NMFyIrPw',
			            //'url' => $cz_url,
			            'data' => [
			                'title' => ['value' => '开课通知！','color' => '#173177'],
			                'userName' => ['value' => $userinfo['nickname'],'color' => '#173177'],
			                'courseName' => ['value' => $lessonName,'color' => '#173177'],
			                'date' => ['value' => date('Y-m-d H:i:s',$openLessonTime),'color' => '#173177'],
			                
			            ]
			        ];

			        $data = json_encode($message);

			        self::doRequest($url,$data);
		        }
	    	}
    	}
    	else
    	{
    		return false;
    	}
    }

    //批改作业
    public static function saveHomework($id)
    {	
    	//获取token
        $token = self::getToken();
        //设置url
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token;
       	$homework = XmVUsersHomework::findOne(['id' => $id]);
       	$userinfo = XmUsers::findOne(['id' => $homework->userId]);
        //设置发送的消息
        $message = [
            'touser' => $userinfo->openid,
            'template_id' => 'UGg9LnAyNobl6QzymBk2XnFJ8YFdSHvGMd3TWwtX53I',
            //'url' => $cz_url,
            'data' => [
                'first' => ['value' => '家长您好，老师已对孩子作业进行批改！','color' => '#173177'],
                'keyword1' => ['value' => date('Y-m-d H:i:s',$homework->createdTime),'color' => '#173177'],
                'keyword2' => ['value' => date('Y-m-d H:i:s',$homework->updatedTime),'color' => '#173177'],
                'remark' => ['value' => $homework->comment,'color' => '#173177']
            ]
        ];

        $data = json_encode($message);

        //发送
        $res = self::doRequest($url,$data);
        return $res;
    }

    /**get请求方式的http请求可带参数
     * @param string $url
     * @param array $param
     * @return mixed
     * @throws \Exception
     */
    public static function httpGet($url, $param = array())
    {
        if(!is_array($param)){
            //这里抛出异常
        }
        $p = '';
        if (!empty($param)) {
            foreach ($param as $key=>$value) {
                $s = $key.'='.$value;
                $p .= '&'.$s;
            }
            $p = substr($p, 1);
            $url = $url.'?'.$p;
        }
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    /** 微信开放平台网站应用接口
     * 根据code获取access_token（调用接口的凭证）
     */
    public static function webAppGetAccessToken($code)
    {
        $param = [
            'appid' => self::$web_app_id,
            'secret' => self::$web_app_secret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $data = self::httpGet(self::$web_app_access_token, $param);
        $data = json_decode($data, true);
        if (!empty($data['errcode'])) {
            return [false, $data];
        }
        return [true, $data];
    }


    //通过unionid检测用户是否存在于我们的用户体系中
    public static function unionidIsExistence($unionid)
    {
        $users = XmUsers::find()->select('account, unionid')->where(['unionid'=>$unionid])->asArray()->one();

        if(empty($users)){
            return false;
        }
        return $users;
    }

    /**
     * 获取用户openid
     * @param code 
     * @return array
     */
    public static function getOpenid($code){
       
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . self::$appid . '&secret=' . self::$appsecret . '&code=' . $code . '&grant_type=authorization_code';
//        $weixin = file_get_contents($url);//通过code换取网页授权access_token
//        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
//        $array = get_object_vars($jsondecode);//转换成数组
//        //$openid = $array['openid'];//输出openid
        $array = self::httpGet($url);
        $array = json_decode($array, true);
        return $array;
    }
    

    /**
     * 用户扫码绑定openid
     * @param code 
     */
    public static function userBindOpenid($code,$id)
    {
        $jsoninfo = self::getOpenid($code);
        if(empty($jsoninfo['errcode']))
        {
//            $model = XmUsers::findOne(['id' => $id]);
//            $model->openid = $jsoninfo['openid'];
//            $model->unionid = $jsoninfo['unionid'];
//            if($model->save())
//            {
//                return array('openid' => $jsoninfo['openid']);
//            }
//            else
//            {
//                return false;
//            }
            $ret = XmUsers::updateAll(['openid'=>$jsoninfo['openid'], 'unionid'=>$jsoninfo['unionid']], ['id'=>$id]);
            if ($ret) {
                return array('openid' => $jsoninfo['openid']);
            }else{
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    //扫码登录时查询不到的用户进行注册或绑定用户操作
    public static function addUsersUnionid($phone, $unionid)
    {
        $users = XmUsers::find()->where(['account'=>$phone])->asArray()->one();
        //首先判断手机号码是否存在于用户表中
        if (!empty($users)) {//若存在于用户表中进行绑定操作
            $ret = XmUsers::updateAll(['unionid'=>$unionid], ['id'=>$users['id']]);
        }else{ //不存在则进行的创建账户的操作
            $nickname = substr($phone, 0, 3) . "****" . substr($phone, 7, 4);
            $ti = time();
            $users = new XmUsers();
            $users->account = $phone;
            $users->phone = $phone;
            $users->password = md5((string)$phone);
            $users->unionid = $unionid;
            $users->nickname = $nickname;
            $users->name = $nickname;
            $users->email = '';
            $users->sex = 0;
            $users->add_time = $ti;
            $users->update_time = $ti;
            $users->intention_level = 'E';
            $users->from_type = 1;
            $users->adder_id = 0;
            $users->status = 1;
            $users->os_from = 'xmsj';
            $users->xmschool_id = 0;
            $users->save();
            $ret = $users->id;
        }
        if ($ret > 0) {//创建或者绑定行为成功之后自动登录
           $data = UserService::wechatLogin($unionid);
           return [true, $data];
        }else{
            return [false];
        }
    }

    /**
     * 获取作业信息
     * @param $compose_id int
     * @return array
     */
    public static function getComposeInfo($compose_id)
    {
        //$compose_id = ComposeService::alphaID($compose_id, true, 8, 'xmw');      
        $data = XmCompose::find()->select('id,user_id,title,img,file,description,like_count,mobile_support,mobile_notice')->where(['id' => $compose_id])->asArray()->one();
        if(empty($data)) return array('code' => 404,'msg' => '作业不存在');
        if(!empty($data['img']))
        {
            $data['img'] = self::changeFormat($data['img']);
        }
        if(!empty($data['file']))
        {
            $data['file'] = self::changeFormat($data['file']);
        } 
        //点赞总数
        $data['like_count'] = intval($data['like_count']);
        //查找用户昵称，头像
        $userinfo = XmUsers::find()->select('nickname,avatar_img')->where(['id' => $data['user_id']])->asArray()->one();
        $data['nickname'] = $userinfo['nickname'];
        if(!empty($userinfo['avatar_img']))
        {
            $data['avatar_img'] = self::changeFormat($userinfo['avatar_img']);
        }
        else
        {
            $data['avatar_img'] = env('DEFAULT_IMG');
        }

        //该用户其他作业，目前取最新五条
        if(!empty($data['user_id']))
        {
            $list = XmCompose::find()->select('id,user_id,title,img,file,description,like_count,mobile_support,mobile_notice')->where(['user_id' => $data['user_id']])->andWhere(['<>','id', $data['id']])->limit(5)->orderBy('id DESC')->asArray()->all();
            if(!empty($list))
            {
                foreach ($list as $k => $v)
                {
                    if(!empty($v['img']))
                    {
                        $v['img'] = self::changeFormat($v['img']);
                    }
                    if(!empty($v['file']))
                    {
                        $v['file'] = self::changeFormat($v['file']);
                    }
                    //将数字ID转换为加密字符串
                    $v['id'] = ComposeService::alphaID($v['id'], false, 8, 'xmw');
                    $v['like_count'] = intval($v['like_count']);
                    $list[$k] = $v;
                    unset($v);
                }
                $data['other'] = $list;
            }
            else
            {
                $data['other'] = '';
            }
        }
        else
        {
            $data['other'] = '';
        }
        
        return $data;
    }

    /**
     * oss文件路径转换
     * @param $arr object
     * @return string
     */
    public static function changeFormat($obj)
    {
        $arr = json_decode($obj,true);
        //判断$arr的数据结构
        if(count($arr) == count($arr,1))
        {
            //一维数组
            $str = $arr['host'] . $arr['name'];
        }
        else
        {
            //二维数组
            $str = $arr[0]['host'] . $arr[0]['name'];
        }
        
        return $str;
    }

    /**
     * 一次返回作品所有数据
     * @param $openid string
     * @param $compose_id string 需转换为数字id
     * @return array
     */
    public static function getTotalInfo($compose_id)
    {   
        //将加密字符串转换为数字ID
        $compose_id = ComposeService::alphaID($compose_id, true, 8, 'xmw');
        //作业基本信息
        $compose_info = self::getComposeInfo($compose_id);
        if(isset($compose_info['code']) && $compose_info['code'] == 404) return $compose_info['msg'];
        //是否对该作品点赞,is_star 1已点赞 2未点赞
        //$star = self::checkUserStar($openid,$compose_id);
        return $compose_info;
        //return array_merge($compose_info,$star);
    }

    /**
     * 检测用户是否对作业点赞 
     * @param $openid string 用户微信
     * @param $compose_id int 作业ID
     * @return int || string || array
     */
    public static function checkUserStar($openid,$compose_id)
    {
        //$compose_id = ComposeService::alphaID($compose_id, true, 8, 'xmw');
        $res = XmWechatComposeZan::find()->select('*')->where(['open_id' => $openid,'compose_id' => $compose_id])->asArray()->one();
        $data = array();
        if(!empty($res) && $res['status'] == 1)
        {
            $data['is_star'] = 1;
        }
        else
        {
            $data['is_star'] = 2;
        }
        return $data;
    }

    /**
     * 对作业点赞
     * @param $openid string 用户微信
     * @param $compose_id string 需转换为数字id
     * @param $status int 0取消点赞 1点赞
     * @return int || bool
     */

    public static function composeStar($openid,$compose_id,$status)
    {
        //将加密字符串转换为数字ID
        $compose_id = ComposeService::alphaID($compose_id, true, 8, 'xmw');
        $res = XmWechatComposeZan::findOne(['open_id' => $openid,'compose_id' => $compose_id]);
        //开启事物
        $transaction = \Yii::$app->db->beginTransaction();
        $star = self::getTotalStar($compose_id,$status);
        if(!empty($res))
        {
            $add_time = time();
            $result = XmWechatComposeZan::updateAll(['status' => $status,'add_time' => $add_time], ['open_id' => $openid,'compose_id' => $compose_id]);
        }
        else
        {
            $model = new XmWechatComposeZan();
            $model->open_id = trim($openid);
            $model->compose_id = intval($compose_id);
            $model->status = $status;
            $model->add_time = time();

            $result = $model->save();
        }

        if($result && $star)
        {
            //事物提交
            $transaction->commit();
            return true;
        }
        else
        {
            //事物回滚
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 作业点赞总数统计 
     * @param $compose_id int 
     * @param $status int
     * @return int || bool
     */
    public static function getTotalStar($compose_id,$status)
    {
        $model = XmCompose::findOne(['id' => $compose_id]);
        if($status == 1)
        {
            return XmCompose::updateALL(['like_count' => $model->like_count + 1], ['id' => $compose_id]);
        }
        else
        {
            return XmCompose::updateALL(['like_count' => $model->like_count - 1], ['id' => $compose_id]);
        }
    }

    /**
     * 拉取微信用户个人信息
     * @param @openid string
     * @return array
     */
    public static function getWechatUserinfo($openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info';
        $token = WechatService::getToken();
        $params = array('access_token' => $token,'openid' => $openid,'lang' => 'zh_CN');
        $json = WechatService::httpGet($url,$params);
        //返回信息
        return json_decode($json,true);
    }

    /**
     * 保存微信用户个人信息
     * @param $arr array 微信用户个人信息
     * @param $wechat_type int 1小码王 2小码世界
     * @return int || bool
     */
    public static function saveWechatUserinfo($arr)
    {
        $openid = $arr['openid'];
        $res = XmWechatUser::findOne(['wechat_openid' => $openid,'wechat_type' => 2]);
        $status = true;
        if(empty($res))
        {
            $model = new XmWechatUser();
            $model->wechat_openid = $openid;
            $model->nickname = $arr['nickname'];
            $model->headimgurl = $arr['headimgurl'];
            $model->sex = (string)$arr['sex'];
            $model->city = $arr['city'];
            $model->province = $arr['province'];
            $model->wechat_type = 2;
            if($model->save())
            {
                $status = true;
            }
            else
            {
                $status = false;
            }
        }
        return $status;
    }

    /**
     * 小码世界绑定微信用户个人信息
     * @param $code string 微信code
     * @return $openid string 小码世界微信用户openid
     */
    public static function bindWechatUser($code)
    {
        $jsoninfo = self::getOpenid($code);
        if(empty($jsoninfo['errcode']))
        {
            $openid = $jsoninfo['openid'];
            if(!empty($openid))
            {
                $userinfo = self::getWechatUserinfo($openid);
                self::saveWechatUserinfo($userinfo);
            }
            return $openid;
        }
    }
}