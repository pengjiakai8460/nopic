<?php
/**
 * Created by PhpStorm.

 * Date: 2017/11/17
 * Time: 16:41
 */

namespace console\controllers;


use Api\services\v1\SmsService;
use common\models\orm\SmsContent;
use yii\console\Controller;
use yii\db\Exception;

class SmsController extends Controller
{
    public function actionBatchSend(string $phone_list_filename, string $tpl_id)
    {
        $context = '17代练全面更新，聚集全网优质订单，王者荣耀每单完成必返5-20元，24小时专业客服为您服务，官网 http://t.cn/Riukurd，微信搜索17服务，安卓下载 http://t.cn/RjWfp2Q ，iOS 下载 http://t.cn/RjWIR7t';
        $smsctx = new SmsContent();
        $smsctx->content = $context;
        if ($smsctx->save()) {
            $tpl_id = $smsctx->id;
        }
        else {
            exit('操作失败，请重试');
        }

        $inputs = fopen($phone_list_filename, 'r');
        $phone_number_list = [];

        while (!feof($inputs)) {
            $phone_number_list[] = trim(fgetss($inputs));
        }

        fclose($inputs);

        // 过滤空行等
        $phone_number_list = array_filter($phone_number_list);

        // 去重
        $phone_number_list = array_flip($phone_number_list);
        $phone_number_list = array_keys($phone_number_list);

        foreach ($phone_number_list as $idx => $phone) {
            // 防止失败
            for (;;) {
                try {
                    echo "send({$idx}) : {$phone}\n";
                    SmsService::send($phone, $tpl_id, []);
                    break;
                } catch (\Exception $e) {
                    echo "retry : {$phone}\n";
                }
            }

            usleep(mt_rand(100, 3000));

            if ($idx === 10000) {
                sleep(600);
            }
        }
    }
}