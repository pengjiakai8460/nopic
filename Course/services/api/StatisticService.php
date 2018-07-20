<?php
namespace Course\services\api;

use common\base\BaseService;
use common\models\orm\XmVTrafficStatistics;
use Course\services\ServiceAbstract;

class StatisticService extends ServiceAbstract
{
    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return StatisticService //必须添加这行注释，用于代码提示
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

    //统计链接pv
    public static function addUrlPv($chn = '' , $url = '')
    {
        $date = date('Y-m-d');
        $date = explode('-', $date);
        $y = rtrim($date[0]);//年
        $m = rtrim($date[1]);//月
        $d = rtrim($date[2]);//日
        $data = XmVTrafficStatistics::find()->where(['year' => $y, 'month' => $m, 'day' => $d, 'chn' => $chn, 'url'=>$url])->asArray()->one();
        if (empty($data)) {
            //添加记录
            $trafficStatistics = new XmVTrafficStatistics();
            $trafficStatistics->count = 1;
            $trafficStatistics->chn = $chn;
            $trafficStatistics->year = $y;
            $trafficStatistics->month = $m;
            $trafficStatistics->day = $d;
            $trafficStatistics->url = $url;
            $trafficStatistics->save();
        } else {
            $count = $data['count'] + 1;
            XmVTrafficStatistics::updateAll(['count' => $count], ['id'=>$data['id']]);
        }
    }
}