<?php

namespace Addons\Aliyun\OSS\SDK2\Result;

include_once '../../Addons/Aliyun/OSS/SDK2/Result/Result.class.php';
/**
 * Class PutSetDeleteResult
 * @package OSS\Result
 */
class PutSetDeleteResult extends Result
{
    /**
     * @return array()
     */
    protected function parseDataFromResponse()
    {
        $body = array('body' => $this->rawResponse->body);
        return array_merge($this->rawResponse->header, $body);
    }
}
