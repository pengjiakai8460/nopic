<?php

namespace common\base\exception;

use common\base\Common;
use yii\base\UserException;

class LogicErrorException extends UserException
{
    /**
     * LogicErrorException constructor.
     */
    public function __construct()
    {
        $data = Common::getResult();

        $message = $data[Common::RESULT_MESS];
        $code = $data[Common::RESULT_CODE];

        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'LogicErrorException';
    }
}