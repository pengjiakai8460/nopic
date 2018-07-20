<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmCompose;
use common\models\orm\XmVClassesCompose;
use common\models\orm\XmVCourseHomework;
use common\models\orm\XmVHomework;
use common\models\orm\XmVUsersHomework;
use OSS\OssClient;
use OSS\Core\OssException;

class ComposeService extends BaseService
{
    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return ComposeService //必须添加这行注释，用于代码提示
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

    //保存homework文件
    public static function saveHomework($fileData, $user_id, array $data)
    {
        $savePath = 'Uploads/xmsj/course/'.date('Ymd').'/';
        //保存sb2文件
        $sb2file = array();
        $sb2file['title'] = $user_id.time().rand(10000,99999).'.sb2';
        $sb2file['url'] = $fileData['fileName1']['tmp_name'];
        $sb2ret = self::upload($sb2file, $savePath);
        $sb2Arr = [[
            'name' => $savePath.$sb2file['title'],
            'type' => 'application/zip',
            'size' => $sb2ret['info']['size_upload'],
            'ext' => ".sb2",
            'savename' => $sb2file['title'],
            'savepath' => $savePath,
            'host'=>'http://xmyj.oss-cn-shanghai.aliyuncs.com/',
            'drive'=>'AliyunOSS'
            ]
        ];
        $sb2Url = json_encode($sb2Arr);
        //保存图片文件
        $imgfile = array();
        $imgfile['title'] = $user_id.time().rand(10000,99999).'.jpg';
        $imgfile['url'] = $fileData['jpegfile']['tmp_name'];
        $imgret = self::upload($imgfile, $savePath);
        $imgArr = [[
            'name' => $savePath.$imgfile['title'],
            'type' => 'image\/jpg',
            'size' => $imgret['info']['size_upload'],
            'ext' => ".jpg",
            'savename' => $imgfile['title'],
            'savepath' => $savePath,
            'host'=>'http://xmyj.oss-cn-shanghai.aliyuncs.com/',
            'drive'=>'AliyunOSS'
        ]
        ];
        $imgUrl = json_encode($imgArr);
        if(!empty($data['uuid'])){//如果uuid存在则是一个作品覆盖操作（这里尚未进行删除作品操作）
            //这里是一个修改操作
//            alphaID($id, false, 8, 'xmw');//id加密
            $uuid = $data['uuid'];
            $compose_id = self::alphaID($uuid, true, 8, 'xmw');//字符串解密
            XmCompose::updateAll(['img'=>$imgUrl, 'file'=>$sb2Url],['id'=>$compose_id]);
            //将作品归类定学员的作业表中
            $data2 = explode(',', $data['homework_id']);//"homework_id,classes_id,chapter_id"字符串格式
            XmVUsersHomework::updateAll(['url' => $sb2ret['info']['url']], ['homeworkId'=>$data2[0], 'classId'=>$data2[1], 'chapterId'=>$data2[2]]);
            return 'OK' . $uuid;
        }else{
            $compose_id = self::saveCompose($user_id, $imgUrl, $sb2Url, $fileData['fileName1']['name']);
            $uuid = self::alphaID((int)$compose_id, false, 8, 'xmw');//id转为字符串
            //将作品归类定学员的作业表中
            $data2 = explode(',', $data['homework_id']);//"homework_id,classes_id,chapter_id"字符串格式
            $ret = self::saveUsersHomework($data2[0], $user_id, $compose_id, 'http://oss.xiaoma.cn/'.$savePath.$sb2file['title'], $data2[1], $data2[2]);
            return 'OK' . $uuid;
        }
    }


    //保存homework文件
    public static function saveHomeworknew($fileData, $user_id, array $data)
    {
        $savePath = 'Uploads/xmsj/course/'.date('Ymd').'/';
        //保存sb2文件
        $sb2file = array();
        $sb2file['title'] = $user_id.time().rand(10000,99999).'.sb2';
        $sb2file['url'] = $fileData['sb2file']['tmp_name']; //sb2 file
        $sb2ret = self::upload($sb2file, $savePath);
        $sb2Arr = [[
            'name' => $savePath.$sb2file['title'],
            'type' => 'application/zip',
            'size' => $sb2ret['info']['size_upload'],
            'ext' => ".sb2",
            'savename' => $sb2file['title'],
            'savepath' => $savePath,
            'host'=>'http://xmyj.oss-cn-shanghai.aliyuncs.com/',
            'drive'=>'AliyunOSS'
        ]
        ];
        $sb2Url = json_encode($sb2Arr);
        //保存图片文件
        $imgfile = array();
        $imgfile['title'] = $user_id.time().rand(10000,99999).'.jpg';
        $imgfile['url'] = $fileData['imgfile']['tmp_name'];
        $imgret = self::upload($imgfile, $savePath);
        $imgArr = [[
            'name' => $savePath.$imgfile['title'],
            'type' => 'image\/jpg',
            'size' => $imgret['info']['size_upload'],
            'ext' => ".jpg",
            'savename' => $imgfile['title'],
            'savepath' => $savePath,
            'host'=>'http://xmyj.oss-cn-shanghai.aliyuncs.com/',
            'drive'=>'AliyunOSS'
        ]
        ];
        $imgUrl = json_encode($imgArr);
        if(!empty($data['postdata']['project_id'])){//如果project_id存在则是一个作品覆盖操作（这里尚未进行删除作品操作）
            //这里是一个修改操作
            $uuid = self::alphaID($data['postdata']['project_id'], false, 8, 'xmw');//id加密
            $compose_id = $data['postdata']['project_id'];
            $project_name = $data['postdata']['project_name'];
//            $compose_id = self::alphaID($uuid, true, 8, 'xmw');//字符串解密
            XmCompose::updateAll(['img'=>$imgUrl, 'file'=>$sb2Url,'title'=>$project_name],['id'=>$compose_id]);
            //将作品归类定学员的作业表中
            $params =  $data['params'];
            $data2 = explode(',', $params['homework_id']);//"homework_id,classes_id,chapter_id"字符串格式
            XmVUsersHomework::updateAll(['url' => $sb2ret['info']['url']], ['homeworkId'=>$data2[0], 'classId'=>$data2[1], 'chapterId'=>$data2[2]]);

        }else{
            $compose_id = self::saveCompose($user_id, $imgUrl, $sb2Url, $data['postdata']['project_name']);
            $uuid = self::alphaID((int)$compose_id, false, 8, 'xmw');//id转为字符串
            //将作品归类定学员的作业表中
            $params =  $data['params'];
            $data2 = explode(',', $params['homework_id']);//"homework_id,classes_id,chapter_id"字符串格式
            $ret = self::saveUsersHomework($data2[0], $user_id, $compose_id, 'http://oss.xiaoma.cn/'.$savePath.$sb2file['title'], $data2[1], $data2[2]);
        }
        return ['project_id'=>$compose_id,'project_name'=>$data['postdata']['project_name'],'uuid'=>$uuid];
    }

    //上传图片到oss
    public static function upload($fileInfo, $savePath) {
        //require_once '../../vendor/aliyuncs/oss-sdk-php/autoload.php';
        $accessKeyId = env("AccessKeyId");
        $accessKeySecret = env("AccessKeySecret");
        $endpoint = env("Endpoint");
        $bucket = env("Bucket");
        $object = $savePath. $fileInfo['title'];
        $file = $fileInfo['url'];
        $options = array();
        $ret = [];
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false );
            $ret = $ossClient->uploadFile($bucket, $object, $file, $options);
//            file_put_contents('/composeUpload.log', $ret,FILE_APPEND);
            return $ret;
        } catch (OssException $e) {
            printf($e->getMessage() . "\n");
            return;
        }

        return $ret;
    }

    public static function saveCompose($user_id, $imgUrl, $fileUrl, $title)
    {
        //保存对应的作品记录
        $compose = new XmCompose();
        $compose->title = $title;
        $compose->user_id = $user_id;
        $compose->img = $imgUrl;
        $compose->file = $fileUrl;
        $compose->status = 1;
        $compose->is_ssue = 0;
        $compose->type = 2;//课后作业
        $compose->add_time = time();
        $compose->update_time = time();
        $compose->release_time = time();
        $compose->save();
        //生成compose记录后将作品id保存到
        return $compose->id;
    }

    //保存作品的id到班级中去
//    private static function saveClassCompose($users_id, $homework_id, $compose_id)
//    {
//        $classCompose = new XmVClassesCompose();
//        $classCompose->createTime = time();
//        $classCompose->updateTime = time();
//        $classCompose->status = 1;//1表示小节的作业
//        $classCompose->composeId = $compose_id;
//        $classCompose->type = 1;
//        $classCompose->homeworkId = $homework_id;
//        $classCompose->usersId = $users_id;
//        $classCompose->save();
//        return $classCompose->id;
//    }

    //crm拷贝过来的加密揭秘方式
    /**
     * 数字ID转换成字母ID
     * 实例：
     * alphaID(12354）；  //会将数字转换为字母。
     * alphaID('PpQXn7COf',true）；//会将字母ID转换为对应的数字。
     * alphaID(12354,false,6）；//指定生成字母ID的长度为6.
     * @param unknown $in
     * @param string $to_num
     * @param string $pad_up
     * @param unknown $passKey
     * @return string
     */
    public static function alphaID($in, $to_num = false, $pad_up = false, $passKey = null){
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($passKey !== null) {

            for ($n = 0; $n<strlen($index); $n++) {
                $i[] = substr( $index,$n ,1);
            }

            $passhash = hash('sha256',$passKey);
            $passhash = (strlen($passhash) < strlen($index))
                ? hash('sha512',$passKey)
                : $passhash;

            for ($n=0; $n < strlen($index); $n++) {
                $p[] =  substr($passhash, $n ,1);
            }

            array_multisort($p,  SORT_DESC, $i);
            $index = implode($i);
        }

        $base  = strlen($index);

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $in  = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number  -->>  alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a   = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in  = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;
    }

    //根据homework_id获取作品对应的素材oss地址
    public static function homeworkUrl($users_id, $homework_id, $class_id, $chapter_id)
    {
        //首先判断学员是否已经做了这个作业，如果已经做了则返回对应作品的url地址
        $usersHomework = XmVUsersHomework::find()->where(['chapterId' => $chapter_id, 'homeworkId'=>$homework_id, 'classId'=>$class_id, 'userId'=>$users_id, 'is_delete'=>1])->asArray()->one();
        if (!empty($usersHomework)) {
            $compose = XmCompose::find()->where(['id' => $usersHomework['composeId']])->asArray()->one();
            $compose_id = (int)$usersHomework['composeId'];
            $uuid = self::alphaID($compose_id, false, 8, 'xmw');
            $sb2 = json_decode($compose['file'], true);
            if(empty($sb2[0]['name'])){
                $url = 'http://oss.xiaoma.wang/'.$sb2['name'];
            }else{
                $url = 'http://oss.xiaoma.wang/'.$sb2[0]['name'];
            }
            //20180711 linm 增加作品未加密的id
            return [
//                'url'  => $usersHomework['url'],
                'url' =>$url,
                'uuid' => $uuid,
                'id' => $usersHomework['homeworkId'],
                'title' => $compose['title'],
                'project_id'=>$compose_id
            ];
        }else{
            $homework = XmVHomework::find()->select('id, title, url')->where(['id'=>$homework_id])->asArray()->one();
            if (!empty($homework)) {
                $homework['uuid'] = '';
                $homework['project_id'] = '';
                return $homework;
            }
            return [];
        }

    }

    //保存作业记录
    public static function saveUsersHomework($homework_id, $users_id, $compose_id, $url, $classes_id, $chapter_id)
    {
        //检查是否已经存在对应的作业记录若存在则进行变更操作
        $usersHomework = XmVUsersHomework::find()->where(['homeworkId'=>$homework_id, 'userId'=>$users_id, 'classId'=>$classes_id, 'chapterId'=>$chapter_id, 'is_delete'=>1])->asArray()->one();
        if (!empty($usersHomework)) {
            XmVUsersHomework::updateAll(['composeId'=>$compose_id, 'updatedTime' => time(), 'url'=>$url], ['id'=>$usersHomework['id']]);
            return $usersHomework['id'];
        }

        $usersHomework = new XmVUsersHomework();
        $usersHomework->homeworkId = $homework_id;
        $usersHomework->userId = $users_id;
        $usersHomework->composeId = $compose_id;
        $usersHomework->url = $url;
        $usersHomework->classId = $classes_id;//class
        $usersHomework->chapterId = $chapter_id;//chapter
        $usersHomework->type = 1;
        $usersHomework->status = 1;
        $usersHomework->is_finished = 1;
        $usersHomework->createdTime = time();
        $usersHomework->updatedTime = time();
        $usersHomework->save();
        return $usersHomework->id;
    }

    //变更compose信息
    /**
     * @param array $where
     * @param array $data
     * @return int
     */
    private static function updateCompose(array $where,array $data)
    {
        $ret = XmCompose::updateAll($data, $where);
        return $ret;
    }

    //发布作品的方法
    public static function releaseCompose($uuid, array $data)
    {
        $compose_id = self::alphaID($uuid, true, 8, 'xmw');//字符串解密
        $where = array();
        $updateData = array();
        $where['id'] = $compose_id;
        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'mobile_support', 'mobile_notice'])) {
                $updateData[$key] = $value;
            }
        }
        $data['is_ssue'] = 1;
        $data['update_time'] = time();
        //将作业状态改为已完成
        XmVUsersHomework::updateAll(['is_finished' => 2, 'updatedTime' => time()], ['composeId' => $compose_id]);
        $ret = self::updateCompose($where, $updateData);
        return $ret;
    }

    //作品基本信息
    public static function composeInfo($uuid)
    {
        $compose_id = self::alphaID($uuid, true, 8, 'xmw');//字符串解密
        $composeData = XmCompose::find()->select('id, title, img, mobile_support, is_ssue, mobile_notice, description')->where(['id'=>$compose_id])->asArray()->one();
        if(empty($composeData)){
            return null;
        }
        //对图片地址进行解析
        $composeImg = json_decode($composeData['img'], true);
        if(empty($composeImg[0]['name'])){
           $composeData['img'] = $composeImg['name'];
        }else{
            $composeData['img'] = 'http://oss.xiaoma.wang/'.$composeImg[0]['name'];
        }
        return $composeData;
    }
}