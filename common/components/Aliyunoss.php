<?php
/**
 * @Author: Marte
 * @Date:   2017-09-04 11:31:20
 * @Last Modified by:   Marte
 * @Last Modified time: 2017-09-06 16:19:28
 */
namespace common\components;

use Yii;
use yii\base\Component;
use OSS\OssClient;
use OSS\Croe\OssException;

class Aliyunoss extends Component
{
    public static $oss;

    public function __construct()
    {
        parent::__construct();
        $accessKeyId = Yii::$app->params['oss']['accessKeyId'];                 //获取阿里云oss的accessKeyId
        $accessKeySecret = Yii::$app->params['oss']['accessKeySecret'];         //获取阿里云oss的accessKeySecret
        $endpoint = Yii::$app->params['oss']['endPoint'];                       //获取阿里云oss的endPoint
        self::$oss = new OssClient($accessKeyId, $accessKeySecret, $endpoint);  //实例化OssClient对象
    }

    /**
     * 使用阿里云oss上传文件
     * @param $object   保存到阿里云oss的文件名
     * @param $filepath 文件在本地的绝对路径
     * @return bool     上传是否成功
     */
    public function upload($object, $filepath)
    {
        // $res = false;
        $bucket = Yii::$app->params['oss']['bucket'];  //获取阿里云oss的bucket

        $result=array();
        try{
            $getOssInfo=self::$oss->uploadFile($bucket, $object, $filepath);
            $result['url'] = $getOssInfo['info']['url'];
            if($getOssInfo['info']['url']){
                @unlink(substr($_path, 1));
            }
        }catch(OssException $e){
            var_dump($e);
            return;
        };
        $url=$result['url'];
        return  $url;
        // var_dump("$url");die();

      //     $res = true;
        //     return $url;
    }


    /**
     * 删除指定文件
     * @param $object 被删除的文件名
     * @return bool   删除是否成功
     */
    public function delete($object)
    {
        $res = false;
        $bucket = Yii::$app->params['oss']['bucket'];    //获取阿里云oss的bucket
        if (self::$oss->deleteObject($bucket, $object)){
        //调用deleteObject方法把服务器文件上传到阿里云oss
            $res = true;
        }
        return $res;
    }

//测试
    public function test(){
        echo 123;
        echo "success";
    }
}