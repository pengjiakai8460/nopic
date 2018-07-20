<?php

namespace Course\components\uploader;

interface IUploadHandler
{
    /**
     * @param $path
     * @param $file
     * @return string url
     */
    function upload($path, $file);
}