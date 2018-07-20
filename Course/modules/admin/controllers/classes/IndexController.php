<?php
namespace Course\modules\admin\controllers\classes;

use common\models\orm\XmVClasses;
use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\AdminService;
use Course\services\api\ClassesService;
use Course\services\api\HomeworkService;

class IndexController extends AdminBaseController
{
    public function actionTest()
    {
        $ret = AdminService::$adminInfo;
        var_dump($ret);
        echo 111;
        exit;
    }

    //班级列表包含查询
    public function actionList()
    {
        $page = \Yii::$app->request->get('page', 1);
        $limit = \Yii::$app->request->get('limit', 10);

        $class_name = \Yii::$app->request->get('class_name', '');
        $class_id = \Yii::$app->request->get('class_id', '');
        $start_time = \Yii::$app->request->get('start_time', '');
        $end_time = \Yii::$app->request->get('end_time', '');
        $course_id = \Yii::$app->request->get('course_id','');
        $status = \Yii::$app->request->get('status','');
        $where = [
            'class_name' => $class_name,
            'class_id' => $class_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'course_id' => $course_id,
            'status' => $status
        ];
        $ret = ClassesService::classList($page, $limit, $where);
        return $this->apiResult(200, '成功', $ret);
    }

    //创建班级基本信息
    public function actionCreateClass()
    {
        
        $adminInfo = AdminService::$adminInfo;
        $class_name = \Yii::$app->request->post('class_name');
//        $course_goods_id = \Yii::$app->request->post('course_id');
//        $start_time = \Yii::$app->request->post('start_time');
//        $end_time = \Yii::$app->request->post('end_time');
        $course_goods_id = \Yii::$app->request->post('course_goods_id');
        $creator_id = $adminInfo['uid'];
        //$creator_id = 10134;

        if (!($class_name && $course_goods_id)) {
            return $this->apiResult(301, '参数错误');
        }

        $ret = ClassesService::createClass($class_name, $course_goods_id, $creator_id);
        if ($ret) {
            return $this->apiResult(200, '创建成功', ['class_id'=>$ret]);
        }else{
            return $this->apiResult(300, '创建记录失败');
        };
    }

    //将学员安排到指定班级
    public function actionAddClassUser()
    {
//        $creator = 10134;
        $adminInfo = AdminService::$adminInfo;
        $creator = $adminInfo['uid'];
        $class_id = \Yii::$app->request->post('class_id');
//        $user_id_arr = \Yii::$app->request->post('user_id_arr');
//        $order_id_arr = \Yii::$app->request->post('order_id');
//        $course_id = \Yii::$app->request->post('course_id');//课程id
//        $course_goods_id = \Yii::$app->request->post('course_goods_id');
        $all_id = \Yii::$app->request->post('all_id');
        if(!($class_id && $all_id)){
            return $this->apiResult(300, '参数错误');
        }
        foreach ($all_id as $key => $value) {
            if(empty($value)){
                unset($all_id[$key]);
            }
        }
//        $usersIdArr = ($all_id, true);
        foreach ($all_id as $key => $value) {
            ClassesService::addClassesUsers($class_id, $value['user_id'],$creator, $value['user_course_goods_id']);
        }
        return $this->apiResult(200, '成功');
    }

    //获取需要分班的学员列表
    public function actionEnrolment()
    {
//        $course_id = \Yii::$app->request->get('course_id');
//        $users_id = \Yii::$app->request->get('user_id');//用户id的一个搜索
//        $ret = ClassesService::enrolmentNoClasses($course_id, $users_id);
//        return $this->apiResult(200, '成功', $ret);
        //变更流程传递参数变为course_good_id
        $course_goods_id = \Yii::$app->request->get('course_goods_id');
        $users_id = \Yii::$app->request->get('user_id');
        $ret = ClassesService::enrolmentNoClasses($course_goods_id, $users_id);
        return $this->apiResult(200, '成功', $ret);

    }

    //班级详情页面
    public function actionClassDetail()
    {
        $class_id = \Yii::$app->request->get('class_id');
        if(empty($class_id)){
            return $this->apiResult(300, '参数错误');
        }
        $classDetail = ClassesService::classDetail($class_id);
        return $this->apiResult(200, '加油', $classDetail);
    }

    //开课操作
    public function actionOpenClass()
    {
//        $creator = 10134;
        $adminInfo = AdminService::$adminInfo;
        $creator = $adminInfo['uid'];
        $class_id = \Yii::$app->request->get('class_id');
        $course_id = \Yii::$app->request->get('course_id');
        $lesson_id = \Yii::$app->request->get('lesson_id');
        $ret = ClassesService::addClassesLesson($class_id, $course_id, $lesson_id, $creator);
        if($ret){
            return $this->apiResult(200, '成功', $ret);
        }
        return $this->apiResult(305, '开课操作失败');
    }

    //变更班级时间的方法
    public function actionChangeDate()
    {
        $classes_id = \Yii::$app->request->get('classes_id', false);
        $open_time = \Yii::$app->request->get('open_time', false);
        $close_time = \Yii::$app->request->get('close_time', false);
        if($classes_id && $open_time && $close_time){
            return $this->apiResult(300, '参数错误');
        }
        $ret = ClassesService::updateClassesTime($classes_id, $open_time,$close_time);
        if (!empty($ret)) {
            return $this->apiResult(200, '变更成功');
        } else {
            return $this->apiResult(301, '失败了');
        }
    }

    //关闭班级操作
    public function actionCloseClass()
    {
        $classes_id = \Yii::$app->request->get('class_id');
        if (empty($classes_id)) {
            return $this->apiResult(300, '参数错误');
        }
        $ret = ClassesService::closeClass($classes_id);
        if ($ret) {
            return $this->apiResult(200, '成功');
        }
        return $this->apiResult(500, '变更状态失败');
    }

    //查看学生作业
    public function actionUserHomeworkList()
    {
        $class_id = \Yii::$app->request->get('class_id');
        $users_id = \Yii::$app->request->get('user_id');
        if (!($class_id && $users_id)) {
            return $this->apiResult(300, '参数错误');
        }
        $ret = ClassesService::usersClassHomeworkList($class_id, $users_id);
        return $this->apiResult(200, '成功', ['list'=>$ret[0], 'users'=>$ret[1]]);
    }

    //班级课程进度详情(章节作业进度)
    public function actionLessonSchedule()
    {
        $class = \Yii::$app->request->get('class_id');
        $lesson = \Yii::$app->request->get('lesson_id');
        if (!($class && $lesson)) {
            return $this->apiResult(300, '参数错误');
        }
        $ret = ClassesService::lessonMakeHomework($class, $lesson);
        $ret = ['makeHomeworkList'=>$ret[0], 'noMakeHomeworkUsersList'=> $ret[1], 'lesson' => $ret[2]];
        return $this->apiResult(200, '', $ret);
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

}