<?php
namespace Course\services\api;

use common\models\orm\XmVErrorLogs;
use Course\services\BaseService;

class ErrorLogsService extends BaseService
{
    //添加错误日志记录
    static public function addErrorLog(array $addData)
    {
        $errorLogs = new XmVErrorLogs();
        foreach ($addData as $key => $value) {
            $errorLogs->$key = $value;
        }
        $errorLogs->ip = self::getIp();
        $errorLogs->created_at = time();
        $errorLogs->save();
        return $errorLogs->id;
    }

    /**
     * 获取IP地址
     * @return string
     */
    public static function getIp(){
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
}