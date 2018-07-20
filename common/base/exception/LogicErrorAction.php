<?php

namespace common\base\exception;

use common\base\Common;
use Yii;
use yii\base\ErrorException;
use yii\web\ErrorAction;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * EXTENDS FROM YII BASE ERROR ACTION
 */
class LogicErrorAction extends ErrorAction
{
    /**
     * Runs the action.
     *
     * @return string result content
     */
    public function run()
    {
        Yii::error($this->exception->getMessage(), Yii::$app->requestedRoute);

        Yii::$app->getResponse()->setStatusCodeByException($this->exception);

        if (Yii::$app->getRequest()->getIsAjax()) {
            Yii::$app->getResponse()->setStatusCode(200);
            Yii::$app->getResponse()->format = Response::FORMAT_JSON;

            return Common::getResult();
        }

        if ($this->exception instanceof LogicErrorException) {
            Yii::$app->getResponse()->setStatusCode(200);
            Yii::$app->getResponse()->format = Response::FORMAT_JSON;

            return Common::getResult();
        }

        if ($this->exception instanceof NotFoundHttpException) {
            Yii::$app->getResponse()->setStatusCode(200);
            Yii::$app->getResponse()->format = Response::FORMAT_JSON;

            return Common::setSystemErrorNotThrowException(Common::ERR_INVALID_REQUEST_PATH);
        }

        if ($this->exception instanceof ErrorException) {
            Yii::$app->getResponse()->setStatusCode(200);
            Yii::$app->getResponse()->format = Response::FORMAT_JSON;

            Common::setData($this->exception->getTraceAsString());

            return Common::setErrorNotThrowException($this->exception->getCode(), $this->exception->getMessage());
        }

        return $this->renderHtmlResponse();
    }
}