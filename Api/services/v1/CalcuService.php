<?php

namespace Api\services\v1;

use Codeception\Module\Redis;
use common\models\orm\XmUser;
use Yii;
use common\base\BaseService;
use common\models\orm\XmUserRate;
use common\models\orm\XmReportUserData;

/**
 * Content 统计Service
 */
class CalcuService extends BaseService
{

    public static function calcuData()
    {
        $res = ['code'=>200,'message'=>'','data'=>[]];
        //得到当前时间
        $time = time();
        $date = date('Y-m-d H:i:s',$time);
        $calcuDate = date('Ymd', strtotime("-1 day"));
        $count = XmReportUserData::find()->where(['calcu_date'=>$calcuDate])->count();
        if($count){
            $res['message'] = "执行时间:{$date},输出结果: 已经统计过了";
            return $res;
        }
        //得到所有用户正确率
        $userRateInfo = XmUserRate::find()->where(['status' => 1])->asArray()->all();

        //有数据
        if (!empty($userRateInfo)) {
            $insertData = [];
            foreach ($userRateInfo as $k => $v) {
                $correctRate = ($v['type1_right'] + $v['type2_right'] +
                        $v['type3_right'] + $v['type4_right']) / ($v['type1_all'] + $v['type2_all'] + $v['type3_all'] + $v['type4_all']);
                $correctRate = sprintf("%2d", $correctRate * 100) ;
                $insertData[] = [
                    'user_id' => $v['uid'],
                    'correct_rate' => $correctRate,
                    'calcu_date' => $calcuDate,
                    'add_time' => $time,
                    'update_time' => $time,
                ];
            }

            if ($insertData) {
                \Yii::$app->db->createCommand()->batchInsert(XmReportUserData::tableName(), ['user_id', 'correct_rate', 'calcu_date', 'add_time', 'update_time'], $insertData)->execute();
            }

            $res['message'] = "执行时间:{$date},插入数据库";
            $res['data'] = $insertData;
            return $res;
        }else{
            $res['message'] = "执行时间:{$date},输出结果: 无数据";
            return $res;
        }
    }

}