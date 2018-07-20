<?php

namespace common\base;

use Redis;
use Yii;
class BaseService extends Common
{
    private static $redis = null;
    public static $errors = array();

    /**
     * @param int $errorCode
     * @param string $message
     * @return array
     */
    final public static function error($errorCode = 0, $message = '')
    {
        $errorCode = intval($errorCode);

        if (!empty($message)) {
            $errorMessage = $message;
        } else {
            $errorMessage = static::$errors[$errorCode] ?? null;

            if (!$errorMessage) {
                return self::setSystemError(self::ERR_UNKNOWN_ERRNO);
            }
        }

        return self::setError($errorCode, $errorMessage);
    }

    final public static function getRedis(): Redis
    {
        if (empty(self::$redis)) {
            $redis = new Redis();
            $redis->connect(Yii::$app->redis->hostname, Yii::$app->redis->port);
            $redis->auth(Yii::$app->redis->password);
            $redis->select(Yii::$app->redis->database);

            self::$redis = $redis;
        }

        return self::$redis;
    }


}