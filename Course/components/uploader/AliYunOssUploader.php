<?php

namespace Course\components\uploader;

use OSS\OssClient;

class AliYunOssUploader implements IUploadHandler {
    protected $accessId;
    protected $accessKey;
    protected $endPoint;
    protected $cdnDomain;
    protected $bucket;
    protected $ssl;
    protected $isCname;
    private $client;

    public function getName()
    {
        return (new \ReflectionClass(self::class))->getShortName();
    }

    public function __construct()
    {
        $this->accessId = \Yii::$app->params['oss']['AccessKeyId'];
        $this->accessKey = \Yii::$app->params['oss']['AccessKeySecret'];
        $this->endPoint = \Yii::$app->params['oss']['Endpoint'];
        $this->bucket = \Yii::$app->params['oss']['Bucket'];
        try{
            $this->client = new OssClient(
                $this->accessId,
                $this->accessKey,
                $this->endPoint, false);
        }catch (\Exception $e){}
    }

    public function upload($oss_dir, $file) :string
    {
        $savePath = $oss_dir . '/' . date('Ymd');
        $hash_name = hash_file('sha1', $file) . '.jpg';
        $object = $savePath . '/' . $hash_name;

        $this->client->uploadFile(
                $this->bucket,
                $object,
                $file);

        return \Yii::$app->params['oss']['Request_Url'] . $object;
    }

}