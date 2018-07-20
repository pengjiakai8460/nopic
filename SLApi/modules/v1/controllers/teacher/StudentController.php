<?php
namespace SLApi\modules\v1\controllers\teacher;
use Yii;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\UserService;
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月4日 下午9:51:50 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
class StudentController extends ApiBaseController
{  
    
    private $account_type;
    public $modelClass = 'common\models\orm\XmBUser';
    
    public function init() {
        parent::init();
        $this->account_type = constant('STUDENT_TYPE');
    }
    
    public function actions() {
        $actions = parent::actions();
        unset($actions['update'], $actions['create'], $actions['delete']);
        return $actions;
    }
    
    public function actionFindone() {
        $data = Yii::$app->getRequest()->get();
        $rules = [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
        $this->validate($data, $rules);
        $account_type = $this->account_type;
        $user = UserService::findOne($data['id'], $account_type);
        return $this->success($user);
    }
    
    public function actionList() {
        $data = Yii::$app->getRequest()->get();
        $account = $data['account'] ?? null ;
        $name = $data['name'] ?? null ;
        $page = $data['page'] ?? 1 ;
        $pageSize = $data['limit'] ?? 10;
        $account_type = $this->account_type;
        $list = UserService::getUserList($account, $name, $page, $pageSize, $account_type);
        return $this->success($list);
    }
    
    public function actionCreate() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['school_id', 'name', 'account'], 'required'],
            [['school_id', 'account'], 'integer'],
            [['name'], 'string'],
        ];
        $this->validate($data, $rules);
        $return = UserService::addUser(trim($data['school_id']), trim($data['account']), trim($data['name']), $this->account_type);
        return $this->success($return);
    }
    
    public function actionDelete() {
        $data = Yii::$app->getRequest()->get();
        $rules = [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
        $this->validate($data, $rules);
        $return = UserService::removeUser(trim($data['id']));
        return $this->success($return);
    }
    
    public function actionUpdate() {
        $data = Yii::$app->getRequest()->post();
        unset($data['token']);
        $rules = [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['name'], 'string'],
        ];
        $data['account_type'] = $this->account_type;
        $this->validate($data, $rules);
        $return = UserService::updateUser($data);
        return $this->success($return);
    }
}
 