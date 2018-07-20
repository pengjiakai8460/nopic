<?php

namespace Course\modules\admin\controllers;

use common\base\BaseController;
use common\base\Common;
use Course\modules\behaviors\CourseAdminActionFilter;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

class AdminBaseController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                'class'=>CourseAdminActionFilter::className()
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH',  'OPTIONS'],
                    'Access-Control-Request-Headers' => ['X-Request-With', 'Content-Type'],
                    'Access-Control-Allow-Origin' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                ],

            ]
        ], parent::behaviors());
    }

    public function apiResult($code = 200, $message = '', $data = null)
    {
        header('Content-Type:application/json; charset=utf-8');
        header('Access-Control-Allow-Headers:X-Requested-With, Content-Type');
        header('Access-Control-Allow-Methods:DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT');
        header('Access-Control-Allow-Origin:*');
        header('Connection:keep-alive');
        return $this->result($code, $message, $data);
    }

    public function errorRequestMethod()
    {
        return $this->apiResult(Common::ERR_INVALID_REQUEST_METHOD[Common::RESULT_CODE], Common::ERR_INVALID_REQUEST_METHOD[Common::RESULT_MESS]);
    }

}