<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmUser;
use common\models\orm\XmVOrders;
use common\models\orm\XmVUsersChannel;

class AdminService extends BaseService
{
    private static $_models = array();

    public static $adminInfo;

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author zhangzhicheng
     */
    public static function model($className = __CLASS__){
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            return $model;
        }
    }

    //校验登录并且保存登录的状态到redis上
    public static function login($account, $password)
    {
        $user = XmUser::find()
            ->where([
                'account'=>$account,
                'password'=> md5((string)$password),
                'status' => 1
            ])->asArray()->one();
        if (!empty($user)) {
            $key = md5($account. $password.rand(10000,99999).time());
            $uid = $user['id'];
            $nickname = $user['nickname'];
            $value = json_encode([
                'uid' => $uid,
                'nickname' => $nickname,
            ] );
            RedisService::getRedis()->set('course_admin_'.$key, $value, 7200);
            return [true, $key];
        }else{
            return false;
        }
    }


    //通过token获取登录人员基本信息
    public static function auth($token)
    {
        $ret = RedisService::getRedis()->get('course_admin_'.$token);
        $ret = json_decode($ret, true);
        return $ret;
    }

    //退出操作
    public static function signOut($token)
    {
        $ret = RedisService::getRedis()->delete('course_admin_'.$token);
        return $ret;
    }

    //渠道统计汇总
    public static function channelStatistics()
    {
        //PV统计
        $pv = RedisService::getHash('course_statistics');
        $ret = [];
        if (!empty($pv)) {
            foreach ($pv as $key => $value) {
                $arr = explode('_',$key);
                $ret[$arr[1]][$arr[2]]['pv_count'] = $value;
            }
        }
        //订单渠道统计
        $order = XmVOrders::find()->select('id, paidTime, chn, status')->where(['status'=>'paid'])->andWhere(['!=', 'chn', '0'])->asArray()->all();
        if (!empty($order)) {
            foreach ($order as $key => $value) {
                $date = date('Ymd', $value['paidTime']);
                if (empty($ret[$date][$value['chn']]['orderCount'])){
                    $ret[$date][$value['chn']]['order_count'] = 0;
                };
                $ret[$date][$value['chn']]['order_count'] ++;
            }
        }
        //注册渠道统计
        $users_clannel = XmVUsersChannel::find()->all();
        if (!empty($users_clannel)) {
            foreach ($users_clannel as $key => $value) {
                $date = date('Ymd', $value['create_time']);
                if (empty($ret[$date][$value['channel']]['register_count'])) {
                    $ret[$date][$value['channel']]['register_count'] = 0;
                }
                $ret[$date][$value['channel']]['register_count'] ++;
            }
        }
        return $ret;
    }

    //保存微信外链产生的注册和渠道的关系
    public static function saveUsersChannel($users_id, $chn)
    {
        $users_channel = new XmVUsersChannel();
        $users_channel->channel = $chn;
        $users_channel->users_id = $users_id;
        $users_channel->update_time = time();
        $users_channel->create_time = time();
        $users_channel->save();
    }
}