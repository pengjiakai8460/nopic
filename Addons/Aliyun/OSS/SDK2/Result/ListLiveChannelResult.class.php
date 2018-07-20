<?php

namespace Addons\Aliyun\OSS\SDK2\Result;

use Addons\Aliyun\OSS\SDK2\Model\LiveChannelListInfo;

class ListLiveChannelResult extends Result
{
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $channelList = new LiveChannelListInfo();
        $channelList->parseFromXml($content);
        return $channelList;
    }
}
