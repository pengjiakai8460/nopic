<?php
namespace Course\modules\admin\controllers\index;

use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\AdminService;

class StatisticsController extends AdminBaseController
{
    //渠道统计方法
    public function actionStatisticsList()
    {
        $ret = AdminService::channelStatistics();
//        $ret2 = [];
//        $i = 0;
//        foreach ($ret as $key => $value) {
//            foreach ($value as $k => $v) {
//                $ret2[$i]['date'] =  $key;
//                $ret2[$i]['channel'] = $k;
//                $ret2[$i]['pv_count'] = !empty($v['pv_count']) ? $v['pv_count'] : 0;
//                $ret2[$i]['order_count'] = !empty($v['order_count']) ? $v['order_count'] : 0;
//                $ret2[$i]['register_count '] = !empty($v['register_count ']) ? $v['register_count '] : 0;
//                $i++;
//            }
//        }
        return $this->apiResult(200, '成功', $ret);
    }

    //记录微信端新注册用户的渠道关系
    public function actionUsersChannel()
    {
        $chn = \Yii::$app->request->get('chn');//渠道id
        $users_id = \Yii::$app->request->get('users_id');//用户id
        $check_time = \Yii::$app->request->get('time');//用于校验的时间戳
        $check_str = \Yii::$app->request->get('str');//加密后的校验字符串
        $password = md5('xiaolonglong');
        if (md5($check_time.$password) === $check_str && $chn && $users_id) {//验证操作(来源确定)
            AdminService::saveUsersChannel($users_id, $chn);
        }
    }
}