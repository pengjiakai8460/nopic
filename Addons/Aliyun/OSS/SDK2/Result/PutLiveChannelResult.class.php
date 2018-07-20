<?php

namespace Addons\Aliyun\OSS\SDK2\Result;

use Addons\Aliyun\OSS\SDK2\Model\LiveChannelInfo;

class PutLiveChannelResult extends Result
{
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $channel = new LiveChannelInfo();
        $channel->parseFromXml($content);
        return $channel;
    }
}
