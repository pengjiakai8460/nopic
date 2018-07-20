<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmUser;
use common\models\orm\XmUsers;
use common\models\orm\XmVClasses;
use common\models\orm\XmVClassesLesson;
use common\models\orm\XmVClassesUsers;
use common\models\orm\XmVCourse;
use common\models\orm\XmVCourseChapters;
use common\models\orm\XmVCourseGoods;
use common\models\orm\XmVCourseLessons;
use common\models\orm\XmVHomework;
use common\models\orm\XmVUserCourseGoods;
use common\models\orm\XmVVideos;
use common\models\orm\XmVOrders;
use common\models\orm\XmVUsers;
use common\models\orm\XmVUsersHomework;

class ClassesService extends BaseService
{
    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return ClassesService //必须添加这行注释，用于代码提示
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

    //创建班级基本信息的方法
    public static function createClass($class_name, $course_goods_id, $creator_id)

    {
        $courseGood = XmVCourseGoods::find()->where(['id'=>$course_goods_id])->asArray()->one();
        $classes = new XmVClasses();
        $classes->name = $class_name;
        $classes->course_goods_id = $course_goods_id;
        $classes->is_delete = 1;
        $classes->openTime = $courseGood['available_from'];
        $classes->closeTime = $courseGood['available_to'];
        $classes->creator = $creator_id;//创建者id
        $classes->createdTime = time();
        $classes->updateTime = time();
        $classes->status = 'ongoing';
        $classes->save();
        return $classes->id;
    }

    //获取需要添加进班的人员信息
    public static function enrolmentNoClasses($course_goods_id, $users_id = '')
    {
//        //首先获取所有报名了指定课程，但未分班的人员
//        $order = XmVOrders::find()
//            ->select('userId, paidTime, id as order_id')
//            ->where([
//                'courseId'=>$course_id,
//                'status'=>'paid',//已支付
//                'classesId' => 0//尚且未分班
//            ]);
//        if(!empty($users_id)){
//            $order = $order->andWhere(['userId' => $users_id]);
//        }
//        $order = $order->asArray()->all();
//
//        if(!empty($order)){
//            $orderArr = [];//重组查询的订单数组(以userId为键名)
//            foreach ($order as $key => $value) {
//                $orderArr[$value['userId']] = $value;
//            }
//            $userIdArr = array_column($order, 'userId');
//            $userArr = XmUsers::find()->select('id, name, account')->where(['in', 'id', $userIdArr])->asArray()->all();
//            foreach ($userArr as $key=>$value) {
//                $userArr[$key]['enroll_time'] = $orderArr[$value['id']];
//            }
//            return $userArr;
//        }
//        return [];
        $userCourseGoods = XmVUserCourseGoods::find()
            ->select('user_id')
            ->where(['status' => 0, 'is_deleted' => 0, 'course_goods_id'=>$course_goods_id])
            ->andWhere(['!=', 'class_id', 0])
            ->asArray()->all();
        if (!empty($userCourseGoods)) {
            $ids = array_column($userCourseGoods, 'user_id');
            $users = XmUsers::find()->select('id, nickname, phone')->where(['in', 'id', $ids])->asArray()->all();
            $usersNickname = array_column($users, 'nickname', 'id');
            $usersPhone = array_column($users,'phone', 'id');
            foreach ($userCourseGoods as $key => $value) {
                $userCourseGoods[$key]['user_name'] = $usersNickname[$value['user_id']];
                $userCourseGoods[$key]['phone'] = $usersNickname[$value['user_id']];
            }
        }
        return $userCourseGoods;
    }

    //添加人员到班级中
    public static function addClassesUsers($classes_id, $users_id, $creator, $order_id)
    {
        $classesUser = new XmVClassesUsers();
        $classesUser->classId = $classes_id;
        $classesUser->usersId = $users_id;
        $classesUser->creator = $creator;
        $classesUser->createdTime = time();
        $classesUser->status = 1;
        $classesUser->updateTime = time();
//        $classesUser->courseId = $course_id;
        $classesUser->save();
        $ret = $classesUser->id;
        //分配完班级之后将订单表中班级字段指向该班级的id
        if($ret){
//            XmVOrders::updateAll(['classesId'=>$classes_id], ['id'=>$order_id]);
            XmVUserCourseGoods::updateAll(['']);
        }
        return $ret;
    }

    //班级列表
    public static function classList($page = 1, $limit = 10, array $where)
    {

        $classes = XmVClasses::find()->select('createdTime, id, creator, status, name, course_goods_id')->where(['is_delete'=>1]);

        if (!empty($where['class_name'])) {
            $classes = $classes->andWhere(['like', 'name', $where['class_name']]);
        }
        if (!empty($where['start_time']) && !empty($where['end_time'])) {
            $classes = $classes->andWhere(['between', 'createdTime', $where['start_time'], $where['end_time']]);
        }
        if (!empty($where['class_id'])) {
            $classes = $classes->andWhere(['id'=>$where['class_id']]);
        }
        if (!empty($where['course_id'])) {
            //查找与课程id相关的有效商品id
            $courseGoods = XmVCourseGoods::find()->select('id')->where(['course_id'=>$where['course_id']])->where(['>', 'available_to', time()])->asArray()->all();
            if (!empty($courseGoods)) {
                $courseGoodIds = array_column($courseGoods, 'id');
                $classes = $classes->andWhere(['in', 'course_goods_id', $courseGoodIds]);
            }
//            $classes = $classes->andWhere(['courseId' => $where['course_id']]);
        }
        if (!empty($where['status'])) {
            $classes = $classes->andWhere(['status'=>$where['status']]);
        }
        $classes2 = clone $classes;
        $count = $classes2->count('id');
        $offset = ($page-1)*$limit;
        $classes = $classes->orderBy('id desc')->offset($offset)->limit($limit)->asArray()->all();
        if(!empty($classes)){

            $creatorIds = array_column($classes, 'creator');
            $adminUser = XmUser::find()->select('id, nickname')->where(['in', 'id', $creatorIds])->asArray()->all();
            $adminUsers = array_column($adminUser, 'nickname', 'id');
            $courseGoodIds = array_column($classes, 'course_goods_id');
            $courseGood = XmVCourseGoods::find()->select('id, name')->where(['in', 'id', $courseGoodIds])->asArray()->all();
            $courseGoods = array_column($courseGood, 'name', 'id');
            foreach ($classes as $key => $value) {
                $classes[$key]['add_name'] = $adminUsers[$value['creator']];
                $classes[$key]['classes_id'] = $value['id'];
                $classes[$key]['course_goods_name'] = $courseGoods[$value['course_goods_id']];
            }
//            foreach($classes as $key => $value){
//                $course = XmVCourse::find()->where(['id'=>$value['courseId']])->asArray()->one();
//                $classes[$key]['course_name'] = $course['title'];
//                $adminUser = XmUser::find()->where(['id'=> $value['creator']])->asArray()->one();
//                $classes[$key]['add_name'] = $adminUser['nickname'];
//                $classes[$key]['classes_id'] = $value['id'];
//            }
        }
        return ['count'=>$count, 'list'=>$classes];
    }

    //班级学员列表
    public static function classUsersList($class_id)
    {
        $classesUser = XmVClassesUsers::find()->where(['classId'=>$class_id, 'status'=>1])->orderBy('id desc')->asArray()->all();
        foreach ($classesUser as $key => $value) {
            $users = XmUsers::find()->where(['id'=>$value['usersId']])->asArray()->one();
            $classesUser[$key]['userName'] = $users['nickname'];
            $user = XmUser::find()->where(['id'=>$value['creator']])->asArray()->one();
            $classesUser[$key]['creatorName'] = $user['nickname'];
        }
        return $classesUser;
    }

    //班级课程列表及进度
    public static function courseScheduleList($class_id, $course_id)
    {
        //查看课程对应的课的记录
        $lesson = XmVCourseLessons::find()->where(['courseId'=>$course_id])->asArray()->all();

        //查看班级已经开课的记录
        $classLesson = XmVClassesLesson::find()->where(['classesId'=>$class_id])->orderBy('id desc')->all();
        $scheduleLesson = array_column($classLesson, 'lessonId');//已经开课的课的id
        foreach ($lesson as $key => $value) {
            if (in_array($value['id'], $scheduleLesson)) {
                $lesson[$key]['open_class_status'] = 1;
            }else{
                $lesson[$key]['open_class_status'] = 0;
            }
            //本章的作业数量
            $chapter = XmVCourseChapters::find()->select('homeworkId, id')->where(['lessonId'=>$value['id']])->asArray(true)->all();//本章对应的小节
            $homeworkCount = 0;//本章作业数量
            foreach($chapter as $k => $v){
                if ($v['homeworkId'] != 0) {
                    $homeworkCount ++;
                }
            }
            //本章作业完成率
            $classUsersCount = XmVClassesUsers::find()->where(['classId'=>$class_id, 'status'=>1])->asArray(true)->count('id');//班级总人数
            $chapterIdArr = array_column($chapter, 'id');//本章包含小节
            $userHomeworkCount = XmVUsersHomework::find()->where(['classId'=>$class_id, 'is_delete'=>1, 'is_finished'=>2])->andWhere(['in', 'chapterId', $chapterIdArr])->count('id');//本班本章对应已经提交的作业数量

            if ($homeworkCount == 0 || $classUsersCount == 0) {
                $lesson[$key]['homeworkCompletionRate'] = 0;
            }else{
                $lesson[$key]['homeworkCompletionRate'] = round($userHomeworkCount/($classUsersCount*$homeworkCount), 2);
            }
            //优秀作品率
            $excellentHomeworkCourt = XmVUsersHomework::find()->where(['classId'=>$class_id, 'is_delete'=>1, 'is_finished'=>2, 'is_excellent' => 1, 'status'=>2])->andWhere(['in', 'chapterId', $chapterIdArr])->count('id');

            $lesson[$key]['excellentHomeworkRate'] = ($userHomeworkCount == 0) ? 0 : round($excellentHomeworkCourt/($classUsersCount*$homeworkCount), 2);


            $lesson[$key]['homeworkCount'] = $homeworkCount;
            $lesson[$key]['lesson_id'] = $value['id'];
        }
        return $lesson;
    }

    //班级详情
    public static function classDetail($class_id)
    {
        $classes = XmVClasses::find()->select('name, openTime, closeTime, courseId')->where(['id'=>$class_id])->asArray()->one();
        $course = XmVCourse::find()->select('title')->where(['id'=>$classes['courseId']])->asArray()->one();
        $classes['courseName'] = $course['title'];
        //班级学员列表
        $classUsersList = self::classUsersList($class_id);

        //班级课程列表
        $lessonList = self::courseScheduleList($class_id, $classes['courseId']);
        $ret = [
            'classes'=>$classes,
            'usersList' => $classUsersList,
            'lessonList' => $lessonList
        ];
        return $ret;
    }

    //开课行为
    public static function addClassesLesson($class_id, $course_id, $lesson_id, $creator)
    {
        $classesLesson = new XmVClassesLesson();
        $classesLesson->courseId = $course_id;
        $classesLesson->lessonId = $lesson_id;
        $classesLesson->classesId = $class_id;
        $classesLesson->createTime = time();
        $classesLesson->updateTime = time();
        $classesLesson->creator = $creator;
        $classesLesson->save();
        $ret = $classesLesson->id;
        if($ret){
            //发送开课通知
            WechatService::openLessonNotice($ret);
        }
        return $ret;
    }

    //获取班级课程的所有信息
    public static function classesCourseInfo($course_id, $class_id, $users_id)
    {
        $data = XmVCourse::find()->select('id,title,summray,price,picture as image')->where(['id' => $course_id,'is_delete' => 1])->asArray()->one();
        //查看班级已经开课的记录
        $classLesson = XmVClassesLesson::find()->where(['classesId'=>$class_id])->all();
        $scheduleLesson = array_column($classLesson, 'lessonId');//已经开课的课的id
        if(!empty($data)){
            $data['lessons'] = XmVCourseLessons::find()->select('id,title,courseId as course_id')->where(['courseId' => $course_id,'is_delete' => 1])->asArray()->all();
            foreach ($data['lessons'] as $key => $value) {
                if(in_array($value['id'], $scheduleLesson)){
                    $data['lessons'][$key]['open_lesson_status'] = 1;
                }else{
                    $data['lessons'][$key]['open_lesson_status'] = 0;
                }
            }
        }
        //sections
        if(!empty($data['lessons'])){
            foreach($data['lessons'] as $k => $v){
                if(!empty($v)){
                    $v['sections'] = XmVCourseChapters::find()->select('id,title,lessonId as lesson_id,summray, homeworkId as homework_id, videoId as video_id')->where(['lessonId' => $v['id'],'is_delete' => 1])->asArray()->all();
                    foreach ($v['sections'] as $key => $value) {
                        $video = XmVVideos::find()->where(['id'=>$value['video_id']])->asArray()->one();
                        $homework = XmVHomework::find()->where(['id'=>$value['homework_id']])->asArray()->one();
                        //判断作业是否已经完成了
                        $usersHomework = XmVUsersHomework::find()->where(['userId'=>$users_id, 'classId'=>$class_id, 'chapterId'=>$value['id'], 'is_delete'=>1])->asArray()->one();
                        if(!empty($usersHomework)){
                            if ( 1 == $usersHomework['is_finished']) {
                                $homework['users_homework_status'] = 1;//做了但没有提交
                            }else{
                                $homework['users_homework_status'] = 2;//做了并且已经提交
                            }

                            if($usersHomework['status'] == 1){
                                $homework['users_homework_correct_status'] = 0;//用户作业批改状态
                            }else{
                                $homework['users_homework_correct_status'] = 1;
                            }
                            $homework['users_homework_id'] = $usersHomework['id'];
                            $homework['users_homework_score'] = $usersHomework['score'];
                            $homework['users_homework_comment'] = $usersHomework['comment'];
                            $homework['users_homework_uuid'] = ComposeService::alphaID($usersHomework['composeId'], false, 8, 'xmw');
                        }else{
                            $homework['users_homework_status'] = 0;//作业未提交
                            $homework['users_homework_correct_status'] = 0;
                        }
                        $v['sections'][$key]['video'] = $video;
                        $v['sections'][$key]['homework'] = $homework;
                    }
                }
                $data['lessons'][$k] = $v;
            }
        }
        return $data;
    }

//    //不传班级的情况下去查找班级再返回课程对应的列表信息
//    public static function classesCourseInfo2($course_id, $users_id)
//    {
//        $classesUser = XmVClassesUsers::find()
//            ->where(['usersId'=>$users_id])
//            ->orderBy('id desc')
//            ->asArray()
//            ->one();
//        $class_id = $classesUser['classId'];
//        return self::classesCourseInfo($course_id, $class_id);
//    }

    public static function updateClassesTime($classes_id, $open_time, $close_time)
    {
        return XmVClasses::updateAll(['openTime'=>$open_time, 'closeTime'=>$close_time], ['id'=>$classes_id]);
    }


    //获取班级id的方法
    public static function getClassId($users_id, $course_id)
    {
        $ret = XmVOrders::find()->where(['userId'=>$users_id, 'courseId'=>$course_id, 'status'=>'paid'])->asArray()->one();
        if(!empty($ret)){
            return $ret['classesId'];
        }else{
            return null;
        }
    }

    /**微信端获取视频播放链接的方法
     * @param $open_id
     * @param $class_id
     * @param $lesson_id
     * @param $video_id
     * @return int
     */
    public static function wechatGetVideoUrl($open_id, $class_id, $lesson_id, $video_id)
    {
        //一，openid => uid
        $users = XmUsers::find()->select('id')->where(['openid'=>$open_id])->asArray()->one();
        if (empty($users)) {
            return 4;//未注册或未绑定微信的用户
        }
        $uid = $users['id'];
        //二，uid是否在class中
        $classesUsers = XmVClassesUsers::find()->where(['usersId'=>$uid, 'classId'=>$class_id, 'status'=>1])->asArray()->one();
        if (empty($classesUsers)) { //如果未被分配到该班级中去则返回无权限观看
            return 1;
        }
        //三，验证班级中这一章节是否已经开课
        $classesLesson = XmVClassesLesson::find()->where(['classesId'=>$class_id, 'lessonId'=>$lesson_id, 'status'=>1])->asArray()->one();
        if (empty($classesLesson)) { //如果class中lesson未开放则返回等待开放
            return 2;
        }
        //四，获取播放链接地址等基本信息
        $video = XmVVideos::find()->select('title, src, videoId, qiniuUrl')->where(['id'=>$video_id])->asArray()->one();
//        $video_url = VideoService::videoPlay($video['videoId']);
//        if(!$video_url){//如果播放链接为空则返回视频不存在
//            return 3;
//        }
        $video_url = $video['qiniuUrl'];
        $video['video_url'] = $video_url;
        return $video;
    }

    /**关闭班级的操作(变更班级记录的状态)
     * @param $classes_id
     * @return int
     */
    public static function closeClass($classes_id)
    {
        $ret = XmVClasses::updateAll(['status' => 'close'], ['id'=>$classes_id]);
        return $ret;
    }

    //查看班级学员作业列表
    public static function usersClassHomeworkList($class_id, $users_id)
    {
        $users = XmUsers::find()->select('nickname, id')->where(['id'=>$users_id])->asArray()->one();
        $usersHomework = XmVUsersHomework::find()
            ->where([
                'classId'=>$class_id,
                'userId'=>$users_id,
                'is_delete' => 1,
                'is_finished' => 2
            ])
            ->all();
        $ret = [];
        foreach ($usersHomework as $key=>$value) {
            $homework = XmVHomework::find()->where(['id'=>$value['homeworkId']])->asArray()->one();
            $ret1 = array();
            $ret1['title'] = $homework['title'];
            $ret1['id'] = $value['id'];
            $ret1['time'] = $value['updatedTime'];
            $ret1['status'] = $value['status'];//1未批改, 2未批改
            $ret1['is_excellent'] = $value['is_excellent'];//1优秀，2不优秀
            $ret[] = $ret1;
        }
        return [$ret, $users];
    }

    //课程章节下作业完成情况的记录
    public static function lessonMakeHomework($class_id, $lesson_id)
    {
        $lesson = XmVCourseLessons::find()->select('title, id')->where(['id'=>$lesson_id])->asArray()->one();
        $ret1 = array();//已经存在的作业记录
        $ret2 = array();//未完成作业的学生
        $ret3 = array();//章的信息
        $ret3 = $lesson;
        //一，查找章节下所有的小节id
        $chapters = XmVCourseChapters::find()->where(['lessonId'=>$lesson_id])->asArray()->all();
        $chaptersIsArr = array_column($chapters, 'id');
        //二，查找班级下所有已经做了作业的记录
        $userHomework = XmVUsersHomework::find()->where(['in', 'chapterId',$chaptersIsArr])->andWhere(['classId'=>$class_id, 'is_finished'=>2])->asArray()->all();//作业记录
        foreach ($userHomework as $key=>$value) {
            $homework = XmVHomework::find()->where(['id'=>$value['homeworkId']])->asArray()->one();
            $users = XmUsers::find()->where(['id'=>$value['userId']])->asArray()->one();
            $ret = [];
            $ret['homework_title'] = $homework['title'];
            $ret['user_id'] = $value['userId'];
            $ret['user_nickname'] = $users['nickname'];
            $ret['homework_time'] = $value['updatedTime'];
            $ret['is_excellent'] = $value['is_excellent'];
            $ret['status'] = $value['status'];
            $ret['user_homework_id'] = $value['id'];
            $ret1[] = $ret;
        }
        //三，查询所有需要做作业的人员id(班级内所有人员)
        $classesUsers = XmVClassesUsers::find()->where(['classId'=>$class_id, 'status'=>1])->asArray()->all();
        $usersArr = array_column($classesUsers, 'usersId');
        //四，所有未做作业的id
        $makeHomeworkUsers = array_unique(array_column($userHomework, 'userId'));
        //五，未做作业的学员id
        $noMakeHomeworkUsers = array_diff($usersArr, $makeHomeworkUsers);
        foreach ($noMakeHomeworkUsers as $key=>$value) {
            $users = XmUsers::find()->where(['id'=>$value])->asArray()->one();
            $arr['phone'] = $users['phone'];
            $arr['user_id'] = $users['id'];
            $arr['nickname'] = $users['nickname'];
            $ret2[] = $arr;
        }
        return [$ret1, $ret2, $ret3];
    }

    //记录学生学习课程的记录
    public static function saveUserStudyRecord()
    {

    }
}
