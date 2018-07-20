<?php

namespace Api\modules\v1\behaviors;


use Api\services\v1\UserService;
use common\base\BaseService;
use common\base\Common;
use common\device\Device;
use common\models\orm\Member;
use Yii;
use yii\base\ActionFilter;
use Api\services\v1\RedisService;

class ApiActionFilter extends ActionFilter
{
    const PV_KEY = "cpp_pv";
    const SSO_TOKEN_URL = "http://sso.xiaomawang.com/sso_auth.php";
    //const SSO_TOKEN_URL = "http://auth.xiaomawang.com/sso_auth.php";
    /**
     * 检查基本信息
     *
     * @param \yii\base\Action $action
     * @return bool|void
     */
    public function beforeAction($action)
    {
        // 过滤需要验证放行的接口
        $operation = $action->controller->id . '/' . $action->id;

        if (!in_array($operation, Yii::$app->params['api_4_third_party'])) {
            // 检查频率限制
            static::_throttle($action);

            $version = Yii::$app->request->post('version');
            if (empty($version) || count(explode('.', $version)) !== 3) {
                //Common::setSystemError(Common::ERR_VERSION_INVALID);
            }

            if (!in_array($operation, Yii::$app->params['without_login'])) {

                $accessToken = Yii::$app->request->get('token', '');
                if (empty($accessToken)) {
                    $accessToken = Yii::$app->request->post('token', '');
                }
                if (empty($accessToken)) {
                    $accessToken = isset($_COOKIE['token_key']) ? $_COOKIE['token_key'] : '';
                }

                if (!empty($accessToken)) {
                    //Yii::$app->user->identity = Member::findIdentityByAccessToken($accessToken, 'sso');
                    $url = env('SSO_TOKEN_URL')."?token=" . $accessToken;
                    $res = file_get_contents($url);
                    $tokens = json_decode($res, true);
                    if (isset($tokens['result']['info'])) {
                        $info = json_decode($tokens['result']['info'], true);
                        $uid = isset($info['id']) ? $info['id'] : '';
                        $info['avatar_img'] = isset($info['avatar_img']) ? $info['avatar_img'] : UserService::DEFAULT_IMG;
                        if (empty($uid)) {
                            Common::setSystemError(Common::ERR_NOT_LOGIN);
                        } else {
                            UserService::userCount($uid, $info, false);
                        }
                    } else {
                        Common::setSystemError(Common::ERR_NOT_LOGIN);
                    }
                } else {
                    Common::setSystemError(Common::ERR_NOT_LOGIN);
                }
            }
        }

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        // 携带设备 ID
        if (Yii::$app->getRequest()->post('_device')) {
            //$result['_deviceId'] = Device::getDeviceID();
        }

        $day = date("Ymd");

        $r = RedisService::hIncrBy(self::PV_KEY, $day, 1);

        return parent::afterAction($action, $result);
    }

    /**
     * 1. 首先同一个接口同一个设备 3 秒内只能请求一次
     * 2. 同一个设备多长时间内只能请求n，根据传入的参数判断
     *
     * @param $action
     * @param int $limit
     * @param int $expires
     */
    private function _throttle($action, $limit = 60, $expires = 60)
    {
        $model = $action->controller->id;

        if (in_array($model, Yii::$app->params['throttle'])) {

            $device = Device::getDeviceID();
            $redis = BaseService::getRedis();

            // 首先同一个接口同一个设备 3 秒内只能请求一次
            $key = "throttle:{$device}:{$model}";

            if (false == $redis->setnx($key, 1)) {
                Common::setSystemError(Common::ERR_RATE_ERROR_PER_PATH);
            }

            $redis->expire($key, 1);

            // 同一个设备多长时间内只能请求n，根据传入的参数判断
            $key = "throttle:{$device}:count";

            $count = $redis->incr($key);
            if (1 == $count) {
                $redis->expire($key, $expires);
            } elseif ($count > $limit) {
                Common::setSystemError(Common::ERR_RATE_ERROR_COUNT);
            }
        }
    }
}
