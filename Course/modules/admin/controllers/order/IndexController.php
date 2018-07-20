<?php
namespace Course\modules\admin\controllers\order;

use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\OrderService;

class IndexController extends AdminBaseController
{
    //订单列表（包含查询）
    public function actionList()
    {
        //draft未支付，paid已支付，cancel取消
        $page = \Yii::$app->request->get('page', 1);
        $limit = \Yii::$app->request->get('limit', 10);
        $order_id= \Yii::$app->request->get('order_id');//订单id
//        $create_time = \Yii::$app->request->get('create_time','');
        $order_no = \Yii::$app->request->get('order_no','');
        $user_id = \Yii::$app->request->get('user_id','');
        $user_name = \Yii::$app->request->get('user_name','');
        $start_time = \Yii::$app->request->get('start_time', '');
        $end_time = \Yii::$app->request->get('end_time', '');
        $phone = \Yii::$app->request->get('phone', '');
        $where = array(
            'order_id' => $order_id,
//            'create_time' => $create_time,
            'order_no' => $order_no,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'phone' => $phone
        );
        $ret = OrderService::orderList($page, $limit, $where);
        return $this->apiResult(200, '请求成功', $ret);
    }
}