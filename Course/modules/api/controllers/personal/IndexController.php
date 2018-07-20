<?php
namespace Course\modules\api\controllers\personal;

use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\CourseService;
use Course\services\api\OrderService;
use Course\services\api\PersonalService;
use Course\services\api\UserService;
use Faker\Provider\Person;

class IndexController extends ApiBaseController
{
    //订单列表
    public function actionOrderList()
    {
        $userInfo = UserService::$userInfo;
        $uid= $userInfo['uid'];
        $ret = OrderService::usersOrderList($uid);
        return $this->apiResult(200, '成功', ['list' => $ret]);
    }

    //课程列表
    public function actionCourseList()
    {
        $userInfo = UserService::$userInfo;
        $uid= $userInfo['uid'];
        $ret = CourseService::usersCourseList($uid);
        return $this->apiResult(200, '成功', ['list' => $ret]);
    }

    //我的作品
    public function actionComposeList()
    {
        $info = UserService::$userInfo;
        $uid = $info['uid'];
        $type = \Yii::$app->request->get('type');
        $title = \Yii::$app->request->get('title');
        $static_time = \Yii::$app->request->get('static_time');
        $page = \Yii::$app->request->get('page', 1);
        $limit = \Yii::$app->request->get('limit', 10);
        $where = array();
        $where['type'] = $type;
        $where['title'] = $title;
        $where['static_time'] = $static_time;
        $ret = PersonalService::usercomposeList($uid, $page, $limit, $where);
        return $this->apiResult(200, '成功', $ret);
    }

    //个人中心页面头部需要的信息接口
    public function actionPersonalBasicInfo()
    {
        $uid = UserService::$userInfo['uid'];
        $data = PersonalService::usersBasicInfo($uid);
        return $this->apiResult(200, '成功', $data);
    }

    //删除作品信息
    public function actionDeleteCompose()
    {
        $usersInfo = UserService::$userInfo;
        $uid = $usersInfo['uid'];
        $compose_id = \Yii::$app->request->get('compose_id');
        if (empty($compose_id)) {
            return $this->apiResult(300,'参数错误');
        }
        $ret = PersonalService::deleteCompose($compose_id, $uid);
        if($ret === false){
            return $this->apiResult(308, '无全删除该作品');
        }
        if ($ret) {
            return $this->apiResult(200, '删除成功');
        }
        return $this->apiResult(305, '删除失败');
    }
}