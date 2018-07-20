<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmUser;
use common\models\orm\XmUsers;
use common\models\orm\XmVCourse;
use common\models\orm\XmVOrders;

class OrderService extends BaseService
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

    //后台订单列表（包含查询）
    public static function orderList($page = 1, $limit = 10, array $where)
    {
        //draft未支付，paid已支付，cancel取消
        $select = 'id as orderId, orderSn as orderNumber, userId, courseId, price, createdTime';
        $order = XmVOrders::find()->select($select)->where(['status'=>'paid']);
        if (!empty($where['order_id'])) {//订单id
            $order = $order->andWhere(['id' => $where['order_id']]);
        }
//        if (!empty($where['create_time'])) {//创建时间
//            $order = $order->andWhere(['>=', 'createdTime', $where['create_time']]);
//        }
        if (!empty($where['start_time']) && !empty($where['end_time'])) {
            $where['end_time'] = $where['end_time'] + 24*60*60-1;
            $order = $order->andWhere(['between', 'createdTime', $where['start_time'], $where['end_time']]);
        }
        if (!empty($where['order_no'])) {//订单号
            $order = $order->andWhere(['orderSn' => $where['order_no']]);
        }
        if (!empty($where['user_id'])) {//用户id
            $order = $order->andWhere(['userId'=>$where['user_id']]);
        }
        if (!empty($where['user_name'])) {
            $users = XmUsers::find()->where(['like', 'nickname', $where['user_name']])->asArray()->all();
            $usersIdArr = array_column($users, 'id');
            $order = $order->andWhere(['in', 'userId', $usersIdArr]);
        }
        if (!empty($where['phone'])) {
            $users = XmUsers::find()->where(['phone' => $where['phone']])->asArray()->one();
            $order = $order->andWhere(['userId'=>$users['id']]);
        }

        $order2 = clone $order;
        $count = $order2->count('id');
        $order = $order->orderBy('id desc')->offset(($page-1)*$limit)->limit($limit)->asArray()->all();
        if(!empty($order)){
            foreach($order as $key => $value){
                //待优化
                $course = XmVCourse::find()->where(['id'=>$value['courseId']])->one();
                $order[$key]['courseName'] = $course['title'];
                $users = XmUsers::find()->where(['id'=>$value['userId']])->one();
                $order[$key]['userName'] = $users['nickname'] ?? 'test_name';
                $order[$key]['coursePrice'] = $course['price'];
                $order[$key]['phone'] = $users['phone'];
            }
        }
        return ['count'=>$count, 'list'=>$order];
    }

    //个人订单列表
    public static function usersOrderList($users_id)
    {
        $orders = XmVOrders::find()->where(['userId'=>$users_id, 'status' => 'paid'])->asArray()->all();
        if (!empty($orders)) {
            foreach ($orders as $key => $value) {
                $course = XmVCourse::find()->where(['id'=>$value['courseId']])->asArray()->one();
                $orders[$key]['course_title'] = $course['title'];
                $orders[$key]['course_picture'] = $course['picture'];
            }
        }
        return $orders;
    }
}
