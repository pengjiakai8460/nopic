<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/19
 * Time: 13:29
 */

namespace common\models\sms;


use common\base\Common;
use common\models\orm\SmsContent;
use common\models\utils\ArrayUtil;
use common\models\utils\CurlsUtil;
use common\models\utils\FuncUtil;
use Yii;

/**
 * Class MontnetsSms 梦网短信功能
 * @package common\models\sms
 */
class MontnetsSms implements SmsInterface
{
    // 定义参数替换规则，后面可增加到系统配置里，使运维自己来管理
    const TEMPLATE_REPLACE_WORDS = [
        'shop' => '天猫店',
        'order_id' => '订单号',
        'nickname' => '鱼侠昵称',
        'url' => '短链接',
        'password' => '密码'
    ];

    const MW_REQUEST_DEFAULT_HOST = 'http://61.130.7.220:8023/MWGate/wmgw.asmx/MongateSendSubmit';
    const MW_BASE_ERROR_CODE = -10060;
    const MW_REQUEST_DEFAULT_PARAMS = [
        'userId' => 'J52229',
        'password' => '526852',
        'pszMobis' => '',
        'pszMsg' => '',
        'iMobiCount' => 1,
        'pszSubPort' => '*',
    ];

    public function send($phone, $tpl, $params, $platform)
    {
        $content = $this->template($tpl, $params, $platform);
        $host = self::MW_REQUEST_DEFAULT_HOST;
        $requestParams = self::MW_REQUEST_DEFAULT_PARAMS;

        if (isset(Yii::$app->params['mentnets'])) {
            $MWConfig = Yii::$app->params['mentnets'];

            if (isset($MWConfig['params'])) {
                $requestParams = $MWConfig['params'];
            }

            if (isset($MWConfig['host'])) {
                $host = $MWConfig['host'];
            }
        }

        $requestParams['pszMobis'] = $phone;
        $requestParams['pszMsg'] = $content;

        $url = $host . '?' . http_build_query($requestParams);
        $httpType = FuncUtil::getUriProtocol($host);

        $result = CurlsUtil::http_req($httpType, 'get', $url, '');

        $xml = new \SimpleXMLElement($result);
        $data = ArrayUtil::object_array($xml);

        if ($data[0] > self::MW_BASE_ERROR_CODE) {
            return ['suc' => false, 'detail' => $data];
        }

        return $data;
    }

    public function template($tpl, $params, $platform)
    {
        $smsCtx = SmsContent::findOne(['id' => $tpl]);

        if ($smsCtx) {
            $template = $smsCtx->content;

            foreach (self::TEMPLATE_REPLACE_WORDS as $key => $word) {
                if (isset($params[$key])) {
                    // 替换的规则是中文或者英文的括号及其所包含的内容如 （短链接）或者 (短链接)
                    $pattern = '/(\(|（|\{)' . $word . '(\)|）|\})/i';
                    $replacement = $params[$key];

                    $template = preg_replace($pattern, $replacement, $template);
                    unset($params[$key]); // 过滤掉为后面的替换准备
                }
            }

            foreach ($params as $word => $replacement) {
                $pattern = '/(\(|（|\{)' . $word . '(\)|）|\})/i';
                $template = preg_replace($pattern, $replacement, $template);
            }
        }

        return $template ?? '';
    }
}