<?php

namespace Course\handlers;

use Course\components\uploader\AliYunOssUploader;

class UploadHandler
{
    private $client;
    private $map = [
        'oss' => AliYunOssUploader::class,
    ];

    /**
     * UploadHandler constructor.
     * @param string $driver driver name
     */
    public function __construct($driver = 'oss')
    {
        return $this->disk($driver);
    }

    public function disk($driver)
    {
        //TODO 添加多存储驱动 默认OSS存储
        if (in_array($driver, array_keys($this->map))) {
            $this->client = new $this->map[$driver]();
            return $this;
        }
        return $this->defaultDisk();
    }

    private function defaultDisk()
    {
        $this->client = new AliYunOssUploader();
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }
}