<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/31
 * Time: 17:20
 */

namespace common\models\utils;

use Yii;

class FuncUtil
{
    const HTTP_HEADER = 'http';
    const HTTPS_HEADER = 'https';

    public static function getUriProtocol($uri)
    {
        if (0 === stripos($uri, self::HTTP_HEADER . '://')) {
            return self::HTTP_HEADER;
        }

        if (0 === stripos($uri, self::HTTPS_HEADER . '://')) {
            return self::HTTPS_HEADER;
        }

        return null;
    }

    public static function getAbsoluteUri($uri)
    {
        if (!empty(static::getUriProtocol($uri))) {
            return $uri;
        }

        $uriWithAliCdn = Yii::$app->params['aliyun']['oss']['url'] . $uri;

        return $uriWithAliCdn;
    }

    //版本号和数字进行转换
    public static function verToNum($ver) {

        if (empty($ver)) {
            return 0;
        }
        $vers = explode('.', $ver);
        if (count($vers) != 3) {
            return 0;
        }
        return $vers[0] * 10000 + $vers[1] * 100 + $vers[2];

    }

    /**
     **    控制器中ajax返回
     **
     **/
    public static function ajaxReturn($status = 0, $info = '', $data = array())
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return array(
            'status' => $status,
            'info' => $info,
            'data' => ArrayUtil::parseNull($data)
        );
    }

    /**
     * 过滤处理数据
     *
     */
    public static function parseData($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = self::parseData($v);
                } else {
                    $data[$k] = trim(htmlspecialchars(addslashes($v)));
                }
            }
        } else {
            $data = trim(htmlspecialchars(addslashes($data)));
        }
        return $data;
    }


    /**
     **    解析referer
     **
     **/
    public static function parseReferer()
    {
        $referer = '/site/index/index';
        $refer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if ($refer) {
            $parse = parse_url($refer);
            if (strpos($parse['host'], '.ftm.cn')) {
                $referer = $parse['path'];
            }
        }
        return $referer;
    }
}