<?php
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月3日 下午7:40:18 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
namespace SLApi\modules\v1;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
        /* \Yii::$app->setComponents([
            'response' => [
                'class' => 'yii\web\Response',
                'on beforeSend' => function ($event) {
                    $response = $event->sender;
                    $response->data = [
                        'code' => $response->getStatusCode(),
                        'data' => $response->data,
                        'message' => $response->statusText
                    ];
                    $response->format = \yii\web\Response::FORMAT_JSON;
                },
            ],
        ]); */
    }
}