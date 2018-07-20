<?php

namespace Course\modules\api\controllers;

use common\base\BaseController;
use Course\modules\behaviors\CourseApiActionFilter;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

class ApiBaseController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                'class'=>CourseApiActionFilter::className()
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

    public function apiResult($code, $message, $data = null)
    {
        header('Content-Type:application/json; charset=utf-8');
        header('Access-Control-Allow-Headers:X-Requested-With, Content-Type');
        header('Access-Control-Allow-Methods:DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT');
        header('Access-Control-Allow-Origin:*');
        header('Connection:keep-alive');
        return $this->result($code, $message, $data);
    }
}