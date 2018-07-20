<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/18
 * Time: 16:36
 */

namespace common\models\sms;


interface SmsInterface
{
    /**
     * @param $phone 手机号
     * @param $tpl 模板 ID
     * @param $params 参数
     * @param $platform 平台
     * @return mixed
     */
    public function send($phone, $tpl, $params, $platform);
}