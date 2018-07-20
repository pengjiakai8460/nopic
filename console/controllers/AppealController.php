<?php
/**
 * Created by PhpStorm.

 * Date: 2017/8/15
 * Time: 9:31
 */

namespace console\controllers;

use Api\services\v1\OrderAppealService;
use common\models\orm\OrderAppealRecord;
use common\models\orm\ReleaseOrder;
use yii\console\Controller;


/**
 * Class AppealController
 * @package console\controllers
 */
class AppealController extends Controller
{
    public function actionAppealstatus()
    {
        $data = OrderAppealRecord::find()->where(['status' => 1, 'deleted_at' => 0])->andWhere(['<', 'from_at', time()-(24*60*60)])->all();
        foreach ($data as $k => $v) {
            $v->to_status = 3;
            $v->status = 2;
            $v->save();
            $order = ReleaseOrder::findOne(['order_id' => $v['order_id']]);
            $status = intval($order->status / 100).'21';
            OrderAppealService::insertMongoOrderStatusRecord(0, $v['from_uid'], $v['order_id'], intval($status / 100).'21', '申请仲裁', 0, $order->status, $data['file']??'', 1);
            $order->status = $status;
            $order->save();
        }
    }
}