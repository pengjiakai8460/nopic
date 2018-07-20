<?php
namespace console\controllers;

use Api\services\v1\ReceiverOrderService;
use Yii;
use common\models\orm\ReleaseOrder;
use common\models\orm\MembeseOrder;
use common\models\orm\MemberPcOrder;
use common\models\orm\MemberWealth;
use Api\services\v1\ReleaseOrderService;
use Api\services\v1\OrderService;
use Api\services\v1\CapitalService;
use Api\services\v1\MessageService;
use yii\console\Controller;



class OrderController extends Controller
{
    //自动关闭
    public function actionAutoclose(){
        $uid = OrderService::$sys_uid;//系统
        $ti = time();
        $data = OrderService::getRedis()->zRangeByScore(OrderService::$wait_accept_key, 0, $ti, ['limit' => [0, 200]]);
        //$data = ['PW209250015095905706850', 'PW209067615097791023825'];

        $orders = ReleaseOrder::find()->where(['in', 'order_id', $data])->asArray()->all();

        $ti = time();
        foreach ($orders as $k => $v) {
            if ($v['status'] == OrderService::$order_begin) {
                $ret = OrderService::insertMongoOrderStatusRecord($uid, $v['uid'], $v['order_id'],
                    $status = 720, "超时关闭", "", $v["status"]);

                $fromMemberWealth = MemberWealth::getBalance($v['uid']);
                $fromFreezingMoney = MemberPcOrder::getFreezingMoney($v['uid']);
                $od = ReleaseOrder::findOne(['order_id' => $v['order_id'], 'status' => OrderService::$order_begin, 'uid' => $v['uid']]);
                $od->status = 720;
                $od->update_time = $ti;

                if (!$od->save()) {
                    return self::error(70016);
                }

                //如果是余额支付直接冻结用户资金
                if (!CapitalService::model()->cancelbillOrder($v['uid'], $v['order_id'], $v['price'],
                    $fromMemberWealth, $fromFreezingMoney)) {
                    return self::error(70017);
                }

                //给用户推送消息
                if ($v['order_type'] == OrderService::$order_type_all) {
                    $code = Yii::$app->params['push_code']['match_receive_overtime'] ?? 6;


                } elseif ($v['order_type'] == OrderService::$order_type_person) {
                    $code = Yii::$app->params['push_code']['private_receive_overtime'] ?? 5;
                }
                MessageService::changeOrderStatePush($v['uid'], $v['uid'], $code, $v['order_id']);
                
            }
        }
        OrderService::getRedis()->zRemRangeByScore(OrderService::$wait_accept_key, 0, $ti);
        echo date('y-m-d H:i:s') . '处理超时订单' . "\r\n";exit;
    }

    //自动进行操作
    public function actionAutooperation() {
        $uid = 0;
        $ti = time();
        $data = OrderService::getRedis()->zRangeByScore(OrderService::$waiting_operation, 0, $ti, ['limit' => [0, 200]]);
        if (!empty($data)) {
            foreach ($data as $k => $order_id) {
                $orderData = ReleaseOrder::findOne(['order_id' => $order_id]);
                if (!$orderData) {
                    //return FunctionsUtil::ajaxReturn(0, '查无订单');
                    //return self::error(70001);
                    continue;
                }
                $order = $orderData->attributes;

                //判断订单，用户资金是不是在处理中
                if (!ReceiverOrderService::getcache($order['order_id'], $order['receiver_id'], $order['uid'])) {
                    continue;
                    //return FunctionsUtil::ajaxReturn(0, "订单处理中，请稍后再试");
                    //return self::error(70003);
                }

                $mBanlance1 = MemberWealth::getBalance($uid);
                $mpBanlance1 = MemberPcOrder::getFreezingMoney($uid);


                $mBanlance2 = MemberWealth::getBalance($order['receiver_id']);
                $mpBanlance2 = MemberPcOrder::getFreezingMoney($order['receiver_id']);

                $ti = time();
                $orderData->status = 700;
                $orderData->finish_time = $ti;
                $orderData->update_time = $ti;
                $orderData->re_color = 2;

                //修改逻辑只有待确认的订单 才可以自动确认
                if (in_array($order["status"],[500])) {
                    $outerTransaction = Yii::$app->db->beginTransaction();
                    try {

                        $result = CapitalService::model()->acceptanceCompletion($order['uid'], $order['receiver_id'], $order['order_id'], $order['really_price'], $order['security_deposit'], $order['efficiency_deposit'], $order['add_price'], $order['order_type'], $mBanlance1, $mBanlance2, $mpBanlance2, $mpBanlance1);

                        if (!$result) {
                            continue;
                            //throw new \Exception('资金处理失败');
                            //return self::error(70006);
                        }

                        if (!$orderData->save()) {
                            continue;
                            //throw new \Exception("状态更新失败");
                            //return self::error(70007);
                        }

                        $StatusRecord = OrderService::insertMongoOrderStatusRecord($uid, $order['receiver_id'], $order['order_id'], 700, "已确认", '', $order['status']);
                        if (!$StatusRecord) {
                            continue;
                            //throw new \Exception('记录操作失败');
                            //return self::error(70011);
                        }
                        $outerTransaction->commit();
                    } catch (Exception $e) {
                        $outerTransaction->rollBack();
                        continue;
                        //return self::error(70011);
                    }
                    MemberPcOrder::findOne(['uid' => intval($order['receiver_id'])])->updateCounters(['finish_receive_count' => intval(1)]);
                    MemberPcOrder::findOne(['uid' => intval($order['buyer_id'])])->updateCounters(['finish_release_count' => intval(1)]);

                    $is_buyer = 0;
                    $touid = $order['uid'];

                    if (!empty($touid)) {
                        $code1 = Yii::$app->params['push_code']['confirm_overtime_user'] ?? 10;
                        MessageService::changeOrderStatePush($order['uid'], $order['uid'], $code1, $order['order_id']);

                        $code2 = Yii::$app->params['push_code']['confirm_overtime_anchor'] ?? 11;
                        MessageService::changeOrderStatePush($order['receiver_id'], $order['receiver_id'], $code2, $order['order_id']);


                        OrderService::insertpcmsg(0, (int)$uid, $touid, $order['order_id'], $is_buyer, 700, '', '已确认');
                    }

                    OrderService::getRedis()->zRemRangeByScore(OrderService::$waiting_operation, 0, $ti);
                } else {
                    echo date('Y-m-d H:i:s') . "|Order_id|{$order_id}|处理失败完成操作\r\n";

                }
            }
        }
        echo date('Y-m-d H:i:s') . "处理未完成操作";





    }
}