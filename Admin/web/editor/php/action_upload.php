<?php

include "Uploader.class.php";
//use \OSS\OssClient as OssClient;
//use \OSS\Core\OssException as OssException;
require_once '../../../../vendor/autoload.php';
/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}
/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */
$fileInfo = $up->getFileInfo();
$accessKeyId = "LTAIUsUkhIa68cs9";
$accessKeySecret = "JHH82zsFC3xuJb8ZsQc652C1PLoiEg";
$endpoint = "oss-cn-shanghai.aliyuncs.com";
$bucket = "xmyj";
$object = "Uploads/xmsj/cpp/" . $fileInfo['title'];
$file = "../.." . $fileInfo['url'];
$options = array();

try {
    $ossClient = new OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint, false );
    $ret = $ossClient->uploadFile($bucket, $object, $file, $options);
    $url = $ret['info']['url'] ?? '';
    if (!unlink($file)) {
        unlink($file);
    }
} catch (OSS\Core\OssException $e) {
    //printf(__FUNCTION__ . ": FAILED\n");
    printf($e->getMessage() . "\n");
    return;
}

//$serverName = isset($_SERVER['HTTPS']) ? 'https://'.$_SERVER['SERVER_NAME'] : 'http://'.$_SERVER['SERVER_NAME'];
if(isset($fileInfo['url'])){
    $fileInfo['url'] = $url;
}
/* 返回数据 */
return json_encode($fileInfo);
