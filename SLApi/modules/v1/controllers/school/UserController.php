<?php
namespace SLApi\modules\v1\controllers\school;

use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\UserService;
use Yii;
use SLApi\services\v1\RedisService;
    
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月3日 下午7:53:21 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */

 Class UserController extends ApiBaseController {
     
     public $modelClass = 'common\models\orm\XmBUser';
     
     public function actionLogin() {
         $data = Yii::$app->getRequest()->post();
         $rules = [
             [['account', 'password', 'type', 'school_id'], 'required'],
             [['account', 'password'], 'string'],
             [['type', 'school_id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $res = UserService::login(trim($data['account']), trim($data['password']), trim($data['type']), trim($data['school_id']));
         if ($res['code'] == 200) {
             return $this->apiResult(200, '登陆成功', $res['data']);
         } else {
             return $this->apiResult(0, $res['message']);
         }
     }
     
     public function actionLogout() {
         return $this->apiResult(200, '登出成功');
     }
     
     public function actionAuth() {
         $data = Yii::$app->getRequest()->get();
         $rules = [
             [['token'], 'required'],
             [['token'], 'string'],
         ];
         $this->validate($data, $rules);
         $user = RedisService::getRedis()->get($data['token'] . '-OBJ');
         if (!$user) {
             return $this->apiResult(0, 'please login first');
         } else {
             return $this->success();
         }
     }
 }
 
 
 