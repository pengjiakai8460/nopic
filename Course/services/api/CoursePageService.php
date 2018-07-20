<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmUser;
use common\models\orm\XmVCourse;
use common\models\orm\XmVCoursePage;
use OSS\Core\OssException;
use OSS\OssClient;

class CoursePageService extends BaseService
{
    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return CoursePageService //必须添加这行注释，用于代码提示
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

    //admin端创建课程页的方法
    static public function createCoursePage(array $data)
    {
        //检查是否已经创建了正在使用的课程页面
//        $course_id = $data['course_id'];
//        $coursePage = XmVCoursePage::find()->where(['course_id'=>$course_id, 'status'=>1, 'is_deleted'=>0])->one();
//        if (!empty($coursePage)) {
//            return 2;
//        }
        //数据保存
        $coursePage = new XmVCoursePage();
        foreach ($data as $key => $value) {
            $coursePage->$key = $value;
        }
        $t = time();
        $coursePage -> created_at = $t;
        $coursePage -> updated_at = $t;
        $coursePage -> status     = 0;
        $coursePage -> is_deleted = 0;
        $coursePage -> save();
        $ret = $coursePage->id;
        if ($ret) {
            return 1;
        }
        return 0;
    }

    static public function upCoursePageImg($savePath, $fileUrl, $fileName)
    {
        $savePath = 'Uploads/xmsj/course/'.date('Ymd').'/';
        $accessKeyId = env("AccessKeyId");
        $accessKeySecret = env("AccessKeySecret");
        $endpoint = env("Endpoint");
        $bucket = env("Bucket");
        $object = $savePath. $fileName;
        $file = $fileUrl;
        $options = array();
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false );
            $ossClient->uploadFile($bucket, $object, $file, $options);
            return 'http://oss.xiaoma.wang/'.$object;
        } catch (OssException $e) {
            printf($e->getMessage() . "\n");
            exit;
        }
    }

    //课程页面列表
    static public function coursePageList(array $whereData, $limit = 10, $page = 1)
    {
        $coursePage = XmVCoursePage::find()->select('id, creator, title, cover_img, course_id, ,status, created_at')->where(['is_deleted'=>0]);
        foreach ($whereData as $key => $value) {
            $coursePage->andWhere($value);
        }
        $coursePage2 = clone $coursePage;
        $count = $coursePage2->count();
        $list = $coursePage->orderBy('id desc')->offset($limit*($page-1))->limit($limit)->asArray()->all();
        if (!empty($list)) {
            $creatorIds = array_column($list, 'creator');
            $creator = XmUser::find()->select('id, nickname')->where(['in', 'id', $creatorIds])->asArray()->all();
            $creator = array_column($creator, 'nickname', 'id');
            $courseIds = array_column($list, 'course_id');
            $course = XmVCourse::find()->select('id, title')->where(['in', 'id', $courseIds]);
            $course = array($course, 'title', 'id');
            foreach ($list as $key => $value) {
                $list[$key]['creator'] = $creator[$value['creator']];
//                $list[$key]['cover_img'] = json_decode($value['cover_img'], true);
                $list[$key]['cover_img'] = '一个图片地址';
                $list[$key]['course_name'] = $course[$value['course_id']];
            }
        }
        return [
            'list' => $list,
            'count' => $count
        ];
    }

    static public function getCoursePage($course_page_id)
    {
        $ret = XmVCoursePage::find()->where(['id' => $course_page_id])->asArray()->one();
        return ['detail'=>$ret];
    }

    static public function updateCoursePage($id, $data)
    {
        $ret = XmVCoursePage::updateAll($data, ['id'=>$id]);
        return $ret;
    }

    //切换显示/隐藏状态
    static public function changeCoursePageStatusHide($id)
    {
        $coursePage = XmVCoursePage::find()->where(['id'=>$id])->asArray()->one();
        if ($coursePage['status'] == 1) { //状态为正常显示则进行隐藏操作
            $ret = XmVCoursePage::updateAll(['status'=>0], ['id'=>$id]);
            if ($ret) {
                return 1;//变更成功
            }
            return 0;//数据操作失败
        } else {
            //状态为0则正常显示操作
            //首先进行检查是否可以操作
            $coursePage = XmVCoursePage::find()->where(['is_deleted'=>0, 'status'=>1, 'course_id'=>$coursePage['course_id']])->andWhere(['!=', 'id', $id])->one();
            if (!empty($coursePage)) {
                return -1;//已存在正常使用的课程展示页面导致该操作无法实现
            }
            $ret = XmVCoursePage::updateAll(['status'=>1], ['id'=>$id]);
            if ($ret) {
                return 1;//变更成功
            }
            return 0;//数据操作失败
        }
    }
}