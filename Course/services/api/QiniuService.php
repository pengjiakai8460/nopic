<?php
namespace Course\services\api;

use common\base\BaseService;
use Course\services\api\WechatService;
use crazyfd\qiniu\Qiniu;
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use Qiniu\Config;
use Yii;

class QiniuService extends BaseService
{
    private static $accessKey = 'WZg9LOa6Ow0f9v8C-xahwzVFwsCtxLETrVDcJ9Ui';
    private static $secretKey = 'b0Awn2t3eno0baTuCLHV7L_uF_incF6XNQX6LWYp';
    private static $bucket    = 'xiaoma';
    private static $domain    = 'cdn.xiaoma.wang';
    public static $video_bucket = 'xm-video';
    public static $video_domain = 'http://qiniu.xiaoma.cn/';
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
     * 七牛服务端上传视频
     */
    public static function upload1($data)
    {

        $qiniu = new Qiniu(self::$accessKey, self::$secretKey,'pbzotk8n1.bkt.clouddn.com', 'test','south_china');
        $key = $data['name'];
        $url = $qiniu->uploadFile($data['tmp_name'],$key);
        if(!empty($url))
        {
            return $url;
        }
        else
        {
            return '';
        }
    }

    //分片断点上传
    public static function upload($data)
    {
        //var_dump($data);exit();
        require_once './Qiniu/Config.php';
        require_once './Qiniu/Storage/UploadManager.php';
        $config = new Config();
        $upload = new UploadManager($config);

        $mediaid = date('YmdH') . rand(100000,999999);
        $pipeline = trim('');
        $savekey = self::base64_urlSafeEncode(self::$video_bucket . ':' . $mediaid . '.m3u8');   
        $pfopOps = "avthumb/m3u8/segtime/10/ab/128k/ar/44100/acodec/libfaac/r/30/vb/1000k/vcodec/libx264/stripmeta/0/noDomain/1";
        $pfopOps = $pfopOps . '|saveas/' . $savekey;
        $policy = array(
            'persistentOps' => $pfopOps,
            'persistentPipeline' => $pipeline,
            //'persistentNotifyUrl' => 'http://<your_notify_url>',
        );

        $uploadToken = self::uploadToken(self::$bucket, null, 3600, $policy);
        
        $res = $upload->putFile($uploadToken,$data['name'],$data['tmp_name'],null,'application/octet-stream',false);
        
        if(isset($res[0]['hash']) && !empty($res[0]['hash']))
        {
            return self::$video_domain . $mediaid . '.m3u8';
        }
        else
        {
            return false;
        }
    }

    //签名
    public static function sign($data)
    {
        $hmac = hash_hmac('sha1', $data, 'b0Awn2t3eno0baTuCLHV7L_uF_incF6XNQX6LWYp', true);
        $find = array('+', '/');
        $replace = array('-', '_');
        $encodedData = str_replace($find, $replace, base64_encode($hmac));
        return self::$accessKey . ':' . $encodedData;
    }

    public static function signWithData($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        $encodedData = str_replace($find, $replace, base64_encode($data));
        return self::sign($encodedData) . ':' . $encodedData;
    }

    /**
     * 获取上传token
     * @param $bucket string 
     * @param $key string 文件保存名称
     * @param $expires int 有效期
     * @param $policy array 上传拓展参数
     * @return token string 
     */
    public static function uploadToken($bucket, $key = null, $expires = 3600, $policy = null, $strictPolicy = true)
    {
        $deadline = time() + $expires;
        $scope = $bucket;
        if ($key !== null) 
        {
            $scope .= ':' . $key;
        }

        $args = self::copyPolicy($args, $policy, $strictPolicy);
        $args['scope'] = $scope;
        $args['deadline'] = $deadline;

        $b = json_encode($args);
        return self::signWithData($b);
    }

    /**
     *上传策略，参数规格详见
     * http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html
     */
    private static $policyFields = array(
        'callbackUrl','callbackBody','callbackHost','callbackBodyType','callbackFetchKey','returnUrl','returnBody','endUser','saveKey','insertOnly','detectMime','mimeLimit','fsizeMin','fsizeLimit','persistentOps','persistentNotifyUrl','persistentPipeline','deleteAfterDays','fileType','isPrefixalScope',
    );

    private static function copyPolicy(&$policy, $originPolicy, $strictPolicy)
    {
        if ($originPolicy === null) return array();
        
        foreach ($originPolicy as $key => $value) 
        {
            if (!$strictPolicy || in_array((string)$key, self::$policyFields, true)) 
            {
                $policy[$key] = $value;
            }
        }
        return $policy;
    }

    public static function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    /**
     * 获取七牛视频动态地址,存储空间需设置为私有空间，调用pm3u8属性
     * @param $url string 七牛原始保存地址
     * @return string 视频加密地址
     */
    public static function getPrivateUrl($url)
    {
        require_once './Qiniu/Auth.php';
        $auth = new Auth(self::$accessKey, self::$secretKey);
        return $auth->privateDownloadUrl($url . '?pm3u8/0');
    }

    /**
     * PHP HMAC_SHA1算法
     * @param $str string 待加密字符串
     * @param $key string 加密秘钥
     * @return string 加密后字符串
     */
    public static function getSignature($str, $key) 
    {  
        $signature = "";  
        if (function_exists('hash_hmac')) 
        {
            $signature = bin2hex(hash_hmac("sha1", $str, $key, true));
        } 
        else 
        {
            $blocksize = 64;  
            $hashfunc = 'sha1';  
            if (strlen($key) > $blocksize) 
            {  
                $key = pack('H*', $hashfunc($key));  
            }  
            $key = str_pad($key, $blocksize, chr(0x00));  
            $ipad = str_repeat(chr(0x36), $blocksize);  
            $opad = str_repeat(chr(0x5c), $blocksize);  
            $hmac = pack(  
                    'H*', $hashfunc(  
                            ($key ^ $opad) . pack(  
                                    'H*', $hashfunc(  
                                            ($key ^ $ipad) . $str  
                                    )  
                            )  
                    )  
            );  
            $signature = bin2hex($hmac);
        }  
        return $signature;  
    }
}