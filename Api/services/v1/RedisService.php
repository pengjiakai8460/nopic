<?php

namespace Api\services\v1;

use Yii;
use yii\base\BaseObject;
use common\base\BaseService;
use yii\redis;

/**
 * Redis服务层
 * @author LXX
 */
class RedisService extends BaseService
{

    /**
     * 设置key-value
     * @param $key
     * @param string $value
     * @param string $time
     * @return bool
     */
    public static function setKey($key, $value = "", $time = "")
    {
        if (is_array($key)) return Yii::$app->redis->mset($key);
        return Yii::$app->redis->set($key, $value, $time);
    }

    /**
     * 根据key获取value
     * @param $key
     * @return array|bool|string
     */
    public static function getValueWithKey($key)
    {
        if (is_array($key)) {
            return Yii::$app->redis->mget($key);
        }
        return Yii::$app->redis->get($key);
    }

    /**
     * 删除key
     * @param $key
     * @return mixed
     */
    public static function deleteKey($key)
    {
        return Yii::$app->redis->del($key);
    }

    /**
     * 设置hash
     * @param $hashName
     * @param $key
     * @param string $value
     * @return bool|int
     */
    public static function setHash($hashName, $key, $value = "", $time = 86400, $isExpire = true)
    {
        $isExist = false;
        if (self::getRedis()->exists($hashName)) {
            $isExist = true;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (is_object($v)) {
                    $key[$k] = json_encode($v, JSON_FORCE_OBJECT);
                } elseif (is_array($v)) {
                    $key[$k] = json_encode($v);
                }
            }
            $result = self::getRedis()->hMset($hashName, $key);
        } else {
            if (is_object($value)) {
                $value = json_encode($value, JSON_FORCE_OBJECT);
            } elseif (is_array($value)) {
                $value = json_encode($value);
            };
            self::getRedis()->hdel($hashName, $key);
            $result = self::getRedis()->hset($hashName, $key, $value);
        }
        //20170509 增加 是否过期 参数
        if($isExpire){
            return $isExist ? $result : self::getRedis()->expire($hashName, $time);
        }else{
            return $result;
        }

    }

    /**获取hash
     * @param $hashName
     * @return array|string
     */
    public static function getHash($hashName, $key = "")
    {
        if (self::getRedis()->exists($hashName) == 0 || !self::getRedis()->exists($hashName)) {
            return false;
        }
        if (is_array($key)) {
            Yii::info('hMGet info', 'gethash');
            $data = self::getRedis()->hmget($hashName, $key);
        } elseif ($key) {
            Yii::info('hGet info', 'gethash');
            $data = static::getRedis()->hget($hashName, $key);
        } else {
            Yii::info('hGetAll info', 'gethash');
            $data = self::getRedis()->hgetall($hashName);
        }
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if ($v === '{}') {
                    $data[$k] = new Object();
                } elseif ($v === '[]') {
                    $data[$k] = array();
                } elseif (json_decode($v, true)) {
                    $data[$k] = json_decode($v, true);
                }
            }
        } else {
            if ($data === '{}') {
                $data = new Object();
            } elseif ($data === '[]') {
                $data = array();
            } elseif (json_decode($data, true)) {
                $data = json_decode($data, true);
            }
        }
        return $data;
    }

    /**
     * 删除hash
     * @param $hashName
     * @return int
     */
    public function delHash($hashName)
    {
        return self::getRedis()->del($hashName);
    }

    public static function hIncrBy($hkey, $key, $num = 1) {

        return self::getRedis()->hIncrBy($hkey, $key, $num);
    }

    public static function hSet($hkey, $key, $val) {

        return self::getRedis()->hSet($hkey, $key, $val);
    }




}