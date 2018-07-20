<?php
namespace SLApi\modules\v1\controllers\admin;

use Yii;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\UserService;

/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月11日 下午2:19:31 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
class SchoolController extends ApiBaseController{
    
    private $account_type;
    public $modelClass = 'common\models\orm\XmBUser';
    
    public function init() {
        parent::init();
        $this->account_type = constant('SCHOOL_TYPE');
    }
    
    public function actions() {
        $actions = parent::actions();
        unset($actions['update'], $actions['create'], $actions['delete']);
        return $actions;
    }
    
    public function actionCreate() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['name', 'account'], 'required'],
            [['account', 'provience', 'city', 'area'], 'integer'],
            [['name', 'logo', 'creator'], 'string'],
        ];
        $logo = $data['logo'] ?? '';
        $provience = $data['provience'] ?? 0;
        $city = $data['city'] ?? 0;
        $area = $data['area'] ?? 0;
        $group_id = $data['$roup_id'] ?? 1;
        $creator = $data['creator'] ?? '';
        $this->validate($data, $rules);
        $return = UserService::addSchool(trim($creator), trim($data['account']), trim($data['name']), $this->account_type, trim($logo), 
            trim($provience), trim($city), trim($area), trim($group_id));
        return $this->success($return);
    }
    
    public function actionList() {
        $data = Yii::$app->getRequest()->get();
        $account = $data['account'] ?? null ;
        $name = $data['name'] ?? null ;
        $page = $data['page'] ?? 1 ;
        $pageSize = $data['limit'] ?? 10;
        $list = UserService::getSchoolList($account, $name, $page, $pageSize);
        return $this->success($list);
    }
    
    public function actionFindone() {
        $data = Yii::$app->getRequest()->get();
        $rules = [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
        $this->validate($data, $rules);
        $return = UserService::getSchoolOne(trim($data['id']));
        return $this->success($return);
    }
} 
 
 
 