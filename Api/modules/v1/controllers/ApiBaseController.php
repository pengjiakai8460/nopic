<?php

namespace Api\modules\v1\controllers;

use Api\modules\v1\behaviors\ApiActionFilter;
use common\base\BaseController;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

class ApiBaseController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                'class' => ApiActionFilter::className(),
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH',  'OPTIONS'],
                    'Access-Control-Request-Headers' => ['X-Request-With'],
                    'Access-Control-Allow-Origin' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                ],//

            ]
        ], parent::behaviors());
    }
}