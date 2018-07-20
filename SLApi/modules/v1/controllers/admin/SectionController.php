<?php
namespace SLApi\modules\v1\controllers\admin;

use Yii;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\SectionService;
    /**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月11日 下午8:08:59 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 class SectionController extends ApiBaseController {
     
     public $modelClass  = 'common\models\orm\XmBSection';
     
     public function actions() {
         $actions = parent::actions();
         unset($actions['update'], $actions['create'], $actions['delete']);
         return $actions;
     }
     
     public function actionCreate() {
         $data = Yii::$app->getRequest()->post();
         $rules = [
             [['lesson_id', 'title'], 'required'],
             [['lesson_id', 'sort'], 'integer'],
             [['title', 'summary', 'image'], 'string'],
         ];
         $this->validate($data, $rules);
         $summary = $data['summary'] ?? '';
         $sort = $data['sort'] ?? 1;
         $image = $data['image'] ?? '';
         $content = $data['content'] ?? [];
         if (!empty($content)) {
             $content = json_decode($content, true);
         }
         $return = SectionService::addSection(trim($data['lesson_id']), trim($data['title']), trim($summary), 
             trim($image), trim($sort), $content);
         return $this->success($return);
     }
     
     public function actionFindone() {
         $data = Yii::$app->getRequest()->get();
         $rules = [
             [['id'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $return = SectionService::findOne(trim($data['id']));
         return $this->success($return);
     }
     
     public function actionUpdate() {
         if (!Yii::$app->getRequest()->isPut) {
             return $this->error(0, 'error method');
         }
         $data = Yii::$app->getRequest()->post();
         unset($data['token']);
         $rules = [
             [['id'], 'required'],
             [['id', 'lesson_id', 'sort', 'status'], 'integer'],
             [['title', 'summary', 'image'], 'string'],
         ];
         $this->validate($data, $rules);
         $return = SectionService::updateSection($data);
         return $this->success($return);
     }
     
     public function actionDelete() {
         if (!Yii::$app->getRequest()->isDelete) {
             return $this->error(0, 'error method');
         }
         $data = Yii::$app->getRequest()->get();
         $rules = [
             [['id'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $return = SectionService::deleteSection($data['id']);
         return $this->success($return);
     }

 }
 
 
 