<?php
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月3日 下午7:44:33 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */

namespace SLApi\modules\v1\controllers;

use common\base\BaseController;
use SLApi\modules\v1\behaviors\ApiActionFilter;
use Yii;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use SLApi\modules\v1\behaviors\NewQAuth;
use yii\filters\VerbFilter;

defined('SCHOOL_TYPE') or define('SCHOOL_TYPE', 1);
defined('TEACHER_TYPE') or define('TEACHER_TYPE', 2);
defined('STUDENT_TYPE') or define('STUDENT_TYPE', 3);

class ApiBaseController extends BaseController
{
    public $enableCsrfValidation = false;
    protected $userInfo;
    
    public function behaviors()
    { 
        $res =  ArrayHelper::merge([
            [
                'class'=>ApiActionFilter::className()
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
                
            ],
        ], parent::behaviors());
        
        $res['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'create' => ['post'],
                'findone' => ['get'],
                'list' => ['get'],
                'update' => ['put'],
                'delete' => ['delete'],
            ],
        ];
        
        $operation = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
        $authenticatior = '';
        if (!in_array($operation, Yii::$app->params['without_login'])) {
            return ArrayHelper::merge($res, [
                'authenticator' => [
                    'class' => NewQAuth::className(),
                    'tokenParam' => 'token',
                ],
            ]);
        }
        return $res;
    }
    
    
    public function init() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
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