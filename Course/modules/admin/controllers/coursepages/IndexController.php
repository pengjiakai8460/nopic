<?php
namespace Course\modules\admin\controllers\coursepages;

use common\base\Common;
use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\AdminService;
use Course\services\api\CoursePageService;

class IndexController extends AdminBaseController
{
    //创建课程页面
    public function actionCreateCoursePage()
    {
        $adminInfo = AdminService::$adminInfo;
        $course_id = \Yii::$app->request->post('course_id');
        $title = \Yii::$app->request->post('title');//落地页标题
        $summary = \Yii::$app->request->post('summary');//简介
        $head_img = \Yii::$app->request->post('head_img');//封面图
        $bottom_img = \Yii::$app->request->post('bottom_img');//底图
        $cover_img = \Yii::$app->request->post('cover_img');//封面图
        $detail_img = \Yii::$app->request->post('detail_img');//详情图
        if (!($course_id && $title)) {
            return $this->apiResult(Common::ERR_INVALID_DATA['code'], Common::ERR_INVALID_DATA['message']);
        }
        $data = array();
        $data['title'] = $title;
        $data['course_id'] = $course_id;
        $data['summary'] = $summary;
        $data['head_img'] = $head_img;
        $data['bottom_img'] = $bottom_img;
        $data['cover_img'] = $cover_img;
        $data['creator'] = $adminInfo['uid'];
        $data['detail_img'] = $detail_img;
        $ret = CoursePageService::createCoursePage($data);
        switch ($ret) {
//            case 2:
//                return $this->apiResult(305, '不能创建记录，已经存在正在使用的该课程展示页面');
            case 1:
                return $this->apiResult(200, '创建成功');
            default:
                return $this->apiResult(Common::ERR_UNKNOWN_ERROR['code'], Common::ERR_UNKNOWN_ERROR['message']);
        }
    }

    //课程页面列表
    public function actionCoursePageList()
    {
        if (!\Yii::$app->request->isGet) {
            return $this->apiResult(Common::ERR_INVALID_REQUEST_METHOD['code'], Common::ERR_INVALID_REQUEST_METHOD['message']);
        }
        $page = \Yii::$app->request->get('page', 1);
        $limit = \Yii::$app->request->get('limit', 10);
        //拼装查询条件
        $where = array();
        if ($title = \Yii::$app->request->get('title')) {
            array_push($where, ['like', 'title', $title]);
        }
        if ($course_id = \Yii::$app->request->get('id')) {
            array_push($where, ['id'=>$course_id]);
        }
        if ($start_time = \Yii::$app->request->get('start_time') && $end_time = \Yii::$app->request->get('end_time')) {
            array_push($where, ['between', 'created_at', $start_time, $end_time]);
        }
        if ($status = \Yii::$app->request->get('status')) {
            array_push($where, ['status'=>$status]);
        }
        $ret = CoursePageService::coursePageList($where, $limit, $page);
        return $this->apiResult(200, '成功', $ret);
    }

    //获取单个课程页面基本信息
    public function actionGetCoursePage()
    {
        if (!\Yii::$app->request->isGet) {
            return $this->apiResult(Common::ERR_INVALID_REQUEST_METHOD['code'], Common::ERR_INVALID_REQUEST_METHOD['message']);
        }
        if ($course_page_id = \Yii::$app->request->get('id')) {
            $ret = CoursePageService::getCoursePage($course_page_id);
            return $this->apiResult(200, '成功', $ret);
        }
        return $this->apiResult(Common::ERR_UNKNOWN_ERROR['code'], Common::ERR_UNKNOWN_ERROR['message']);
    }

    //隐藏/可见状态切换
    public function actionChangeCoursePageHide()
    {
        if (!($id = \Yii::$app->request->get('id'))) {
            return $this->apiResult(Common::ERR_INVALID_DATA['code'], Common::ERR_INVALID_DATA['message']);
        }
        $ret = CoursePageService::changeCoursePageStatusHide($id);
        switch ($ret) {
            case 0:
                $ret = Common::ERR_UNKNOWN_ERROR;
                return $this->apiResult($ret['code'], $ret['message']);
            case 1:
                return $this->apiResult(200, '变更成功');
            default:
                return $this->apiResult(305, '已存在使用中的课程展示页面，该操作无法进行');
        }
    }


    //整理上传文件的数组
    private function arrangeUploadFileArr()
    {
        $files = $_FILES;
        $fileData = array();
        foreach ($files as $key => $value) {
            $fileSuffixName = explode('/', mime_content_type($value['tmp_name']));
            if ($fileSuffixName[0] === 'image') {
                $fileSuffixName = $fileSuffixName[1];
            }else{
                $fileSuffixName = $fileSuffixName[0];
            }
//            if (in_array($fileSuffixName, ['png', 'jpg'])) {//检查上传的文件是否为图片格式
//            }
//            $fileData[$key]['file_type'] = mime_content_type($value['tmp_name']);
            $fileData[$key]['file_name'] = md5($key.date('YmdHis')).'.'.$fileSuffixName;
            $fileData[$key]['file_url'] = $value['tmp_name'];
        }
        return $fileData;
    }

    //表单上传图片(可多图)
    public function actionUpImg()
    {
        if (empty($_FILES)) {
            return $this->apiResult(Common::ERR_INVALID_DATA['code'], Common::ERR_INVALID_DATA['message']);
        }
        $folderName = \Yii::$app->request->post('name');//文件夹的名字
        if (empty($folderName)) {
            return $this->apiResult(301, '请传入文件夹名称');
        }
        $savePath = 'Uploads/xmsj/course/coursePage/'.$folderName.'/';
        $imgData = $this->arrangeUploadFileArr();
        $ret = array();
        foreach ($imgData as $key => $value) {
            $img = CoursePageService::upCoursePageImg($savePath, $value['file_url'], $value['file_name']);
            $ret[$key]['imgUrl'] = $img;
        }
        return $this->apiResult(200, '成功', $ret);
    }

    //编辑课程页面记录
    public function actionEditCoursePage()
    {
        if (!($id = \Yii::$app->request->post('id'))) {
            return $this->apiResult(Common::ERR_INVALID_DATA['code'], Common::ERR_INVALID_DATA['message']);
        }
        $data = array();
        $param = array('title', 'course_id', 'head_img', 'bottom_img', 'cover_img', 'detail_img');
        foreach ($param as $item) {
            if ($value = \Yii::$app->request->post($item)) {
                $data[$item] = $value;
            }
        }
        $ret = CoursePageService::updateCoursePage($id, $data);
        if ($ret) {
            return $this->apiResult(200, '更新成功');
        } else {
            $ret = Common::ERR_UNKNOWN_ERROR;
            return $this->apiResult($ret['code'], $ret['message']);
        }
    }
}