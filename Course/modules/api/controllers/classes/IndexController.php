<?php
namespace Course\modules\api\controllers\classes;

use common\models\orm\XmVCourse;
use common\models\User;
use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\ClassesService;
use Course\services\api\HomeworkService;
use Course\services\api\UserService;

class IndexController extends ApiBaseController
{
    public function actionUserinfo()
    {
        UserService::getUserinfo();
    }
    //课程章节列表
    public function actionCourseList()
    {
//        $course_id = \Yii::$app->request->get('course_id');
//        $classes_id = \Yii::$app->request->get('classes_id');
//        if (empty($course_id)) {
//            return $this->apiResult(300, '参数错误');
//        }
//        $ret = ClassesService::classesCourseInfo($course_id, $classes_id);
//        return $this->apiResult(200, '成功', $ret);
        $users = UserService::$userInfo;
        $users_id = $users['uid'];
        $course_id = \Yii::$app->request->get('course_id');
        if (empty($course_id)) {
            return $this->apiResult(300, '参数错误');
        }
        $classes_id = ClassesService::getClassId($users_id, $course_id);
        if(!empty($classes_id)){
            $courseInfoRet = ClassesService::classesCourseInfo($course_id, $classes_id, $users_id);
            $courseInfoRet['classes_id'] = $classes_id;
            return $this->apiResult(200, '成功', $courseInfoRet);
        }else{
            return $this->apiResult(300, '尚未分班');
        }
    }

//    //通过course_id获取课程id
//    public function actionGetClasses()
//    {
//        $users = UserService::$userInfo;
//        $users_id = $users['uid'];
//        $course_id = \Yii::$app->request->get('course_id');
//        $ret = ClassesService::getClassId($users_id, $course_id);
//        if(!empty($ret)){
//            return $this->apiResult(200, '已分班', ['classes_id'=>$ret]);
//        }else{
//            return $this->apiResult(300, '尚未分班');
//        }
//    }

    //微信端播放录播课视频
    /**
     * @return array
     */
    public function actionWechatPlayCourseVideo()
    {
        $open_id = \Yii::$app->request->post('open_id');
        $class_id = \Yii::$app->request->post('class_id');
        $video_id = \Yii::$app->request->post('video_id');
        $lesson_id = \Yii::$app->request->post('lesson_id');
        if (!($open_id && $class_id && $video_id && $lesson_id)) {
            return $this->apiResult(301, '参数错误');
        }
        $ret = ClassesService::wechatGetVideoUrl($open_id, $class_id, $lesson_id, $video_id);
        switch ($ret) {
            case 1;
            return $this->apiResult(302, '无权限观看');
            case 2;
            return $this->apiResult(303, '内容尚未开放');
            case 3;
            return $this->apiResult(304, '视频不存在');
            case 4;
            return $this->apiResult(305, '未注册或未绑定微信的用户');
            default:
                return $this->apiResult(200, '成功', ['video_data'=>$ret]);
        }
    }

    //删除学生作业记录的方法
    public function actionDelUsersHomework()
    {
        $usersHomeworkId = \Yii::$app->request->get('users_homework_id');
        if (empty($usersHomeworkId)) {
            return $this->apiResult(300, '参数错误');
        }
        $ret = HomeworkService::delUsersHomework($usersHomeworkId);
        if ($ret) {
            return $this->apiResult(200, '成功');
        }
        return $this->apiResult(305, '删除失败');
    }

    //保存学生学习记录的id
    public function actionSaveStudyRecord()
    {
        $class_id = \Yii::$app->request->get('class_id');//班级id
        $uid = UserService::$userInfo['uid'];//用户id

    }
}