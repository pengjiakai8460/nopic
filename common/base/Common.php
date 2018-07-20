<?php
/**
 * Created by PhpStorm.

 * Date: 2017/9/18
 * Time: 15:04
 */

namespace common\base;

use common\base\exception\LogicErrorException;
use phpDocumentor\Reflection\Types\Object_;
use yii\base\BaseObject;
use yii\web\Controller;

class Common extends Controller
{
    /**
     * 接口错误码 返回字段定义
     */
    const RESULT_CODE = 'code';
    /**
     * 接口提示信息 返回字段定义
     */
    const RESULT_MESS = 'message';
    /**
     * 接口数据 返回字段定义
     */
    const RESULT_DATA = 'data';
    const RESULT_TIME = 'timestamp';

    /**
     * 系统级错误格式定义
     */

    // 未知错误码
    const ERR_UNKNOWN_ERRNO = [self::RESULT_CODE => 10001, self::RESULT_MESS => '未知的错误码'];
    // 未知错误
    const ERR_UNKNOWN_ERROR = [self::RESULT_CODE => 10002, self::RESULT_MESS => '未知的错误'];
    // 无效参数
    const ERR_INVALID_DATA = [self::RESULT_CODE => 10003, self::RESULT_MESS => '无效的参数'];
    // 签名错误
    const ERR_SIGN_FAILED = [self::RESULT_CODE => 10010, self::RESULT_MESS => '签名错误'];
    // token格式错误
    const ERR_TOKEN_FORMAT = [self::RESULT_CODE => 10011, self::RESULT_MESS => 'token 格式无法解析'];
    // token过期
    const ERR_TOKEN_EXPIRE = [self::RESULT_CODE => 10012, self::RESULT_MESS => 'token 已经过期'];
    // 未登录
    const ERR_NOT_LOGIN = [self::RESULT_CODE => 10021, self::RESULT_MESS => '未登录'];
    // 帐号被禁用
    const ERR_NOT_AVAILABLE = [self::RESULT_CODE => 10022, self::RESULT_MESS => '帐号被禁用'];
    // 个人信息不完整
    const ERR_INCOMPLETE_USER_INFO = [self::RESULT_CODE => 10023, self::RESULT_MESS => '个人信息不完整'];
    // 帐号未认证
    const ERR_NOT_AUTHORIZATION = [self::RESULT_CODE => 10024, self::RESULT_MESS => '帐号未认证'];
    // 帐号未实名认证
    const ERR_NOT_REAL_NAME_AUTH = [self::RESULT_CODE => 10026, self::RESULT_MESS => '帐号未完成实名认证'];
    // 帐号未进行企业认证
    const ERR_NOT_COMPANY_AUTH = [self::RESULT_CODE => 10027, self::RESULT_MESS => '帐号未完成企业认证'];
    // 手机号格式错误
    const ERR_INVALID_MOBILE = [self::RESULT_CODE => 10031, self::RESULT_MESS => '无效的手机号'];
    // 请求方式不对
    const ERR_INVALID_REQUEST_METHOD = [self::RESULT_CODE => 10032, self::RESULT_MESS => '无效的请求方式'];
    // 请求方法不对
    const ERR_INVALID_REQUEST_PATH = [self::RESULT_CODE => 10033, self::RESULT_MESS => '您查找的页面不存在'];
    // 数据验证错误 错误码
    const ERR_VALIDATE_ERROR = 10034;


    const SUCCESS_CODE = 200;


    /**
     * 接口返回数据 meta 定义
     *
     * @var [type]
     */
    private static $result = [
        self::RESULT_CODE => 0,
        self::RESULT_MESS => 'success',
        self::RESULT_DATA => [],
        self::RESULT_TIME => 0
    ];

    private static $otherResult = [];

    /**
     * 设置接口返回数据
     *
     * 错误情况 :
     *  setError() 主要设置meta部分
     *
     * 正确情况 :
     *  setData() 设置返回的数据
     *  addData() 增加数据
     *
     * 增加额外数据
     *  addResult()
     *
     * 返回最终数据
     * getResult()
     *
     */
    /**
     * 设置错误信息
     *
     * @param int $code
     * @param string $message
     * @throws LogicErrorException
     */
    final public static function setError($code = 0, $message = '')
    {
        self::$result[self::RESULT_CODE] = (int)$code;
        self::$result[self::RESULT_MESS] = (string)$message;

        throw new LogicErrorException();
    }

    final public static function setErrorNotThrowException($code = 0, $message = '')
    {
        self::$result[self::RESULT_CODE] = (int)$code;
        self::$result[self::RESULT_MESS] = (string)$message;

        return self::getResult();
    }

    /**
     * 设置返回的message
     *
     * @param string $message
     */
    final public static function setMessage($message = '')
    {
        self::$result[self::RESULT_MESS] = $message;
    }
    /**
     * 填充data数据
     *
     * @param array $data
     * @return array
     */
    final public static function setData($data = [])
    {
        return self::$result[self::RESULT_DATA] = $data;
    }


    /**
     *  设置成功状态码
     * @param int $code
     * @return int
     */
    final public static function setCode($code = 200)
    {
        return self::$result[self::RESULT_CODE] = $code;
    }

    /**
     * 增加data数据
     *
     * @param array $data
     * @return array
     */
    final public static function addData($data = [])
    {
        self::$result[self::RESULT_DATA] = array_merge(self::$result[self::RESULT_DATA], (array)$data);
    }

    /**
     * 获取接口返回数据结构
     *
     * @return array
     */
    final public static function getResult()
    {
        $ret = array_merge(self::$result, self::$otherResult);

        // 空数组转为对象
        if ((is_array($ret[self::RESULT_DATA]) && empty($ret[self::RESULT_DATA]))
            || is_null($ret[self::RESULT_DATA])) {
            $ret[self::RESULT_DATA] = new BaseObject();
        }

        $ret[self::RESULT_TIME] = time();

        return $ret;
    }

    /**
     * 给返回数据增加字段
     *
     * @param array $data
     * @return array
     */
    final public static function addResult($data = [])
    {
        self::$otherResult = array_merge(self::$otherResult, (array)$data);
    }

    /**
     * 设置返回数据的额外字段
     *
     * @param array $data
     * @return array
     */
    final public static function setResult($data = [])
    {
        return self::$otherResult = (array)$data;
    }

    /**
     * 设置系统级错误信息
     *
     * @param int $error
     * @throws LogicErrorException
     */
    final public static function setSystemError($error = 0)
    {
        header('Content-Type:application/json; charset=utf-8');
        header('Access-Control-Allow-Headers:X-Requested-With, Content-Type');
        header('Access-Control-Allow-Methods:DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT');
        header('Access-Control-Allow-Origin:*');
        header('Connection:keep-alive');
        self::$result[self::RESULT_CODE] = (int)$error[self::RESULT_CODE];
        self::$result[self::RESULT_MESS] = (string)$error[self::RESULT_MESS];

        throw new LogicErrorException();
    }

    /**
     * @param int $error
     * @return array
     */
    final public static function setSystemErrorNotThrowException($error = 0)
    {
        self::$result[self::RESULT_CODE] = (int)$error[self::RESULT_CODE];
        self::$result[self::RESULT_MESS] = (string)$error[self::RESULT_MESS];

        return self::getResult();
    }
}