<?php
namespace SLApi\modules\v1\controllers\admin;

use Yii;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\MaterialService;
        /**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月11日 下午8:08:59 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 class MaterialController extends ApiBaseController {
     
     public $modelClass  = 'common\models\orm\XmBMaterial';
     
     public function actions() {
         $actions = parent::actions();
         unset($actions['update'], $actions['create'], $actions['delete']);
         return $actions;
     }
     
     public function actionCreate() {
         $data = Yii::$app->getRequest()->post();
         $rules = [
             [['title', 'type', 'value'], 'required'],
             [['type'], 'integer'],
             [['title', 'value'], 'string'],
         ];
         $this->validate($data, $rules);
         $attr = $data['attr'] ?? [];
         $return = MaterialService::addMaterial(trim($data['title']), trim($data['type']), trim($data['value']), $attr);
         return $this->success($return);
     }
     
     public function actionFindone() {
         $data = Yii::$app->getRequest()->get();
         $rules = [
             [['id'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $return = MaterialService::findOne(trim($data['id']));
         return $this->success($return);
     }
     
     public function actionList() {
         $data = Yii::$app->getRequest()->get();
         $page = $data['page'] ?? 1 ;
         $pageSize = $data['limit'] ?? 10;
         $list = MaterialService::getMaterialList($page, $pageSize);
         return $this->success($list);
     }
     
     public function actionUpdate() {
         if (!Yii::$app->getRequest()->isPut) {
             return $this->error(0, 'error method');
         }
         $data = Yii::$app->getRequest()->post();
         unset($data['token']);
         $rules = [
             [['id'], 'required'],
             [['type'], 'integer'],
             [['title', 'value'], 'string'],
         ];
         $this->validate($data, $rules);
         $data['attr'] = isset($data['attr']) ? json_encode(json_decode($data['attr'], true)) : '';
         $return = MaterialService::updateMaterial($data);
         return $this->success($return);
     }
     
     public function actionDelete() {
         $data = Yii::$app->getRequest()->get();
         $rules = [
             [['id'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $return = MaterialService::deleteMaterial(trim($data['id']));
         return $this->success($return);
     }

 }
 
 
 