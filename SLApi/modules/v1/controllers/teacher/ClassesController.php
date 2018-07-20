<?php
namespace SLApi\modules\v1\controllers\teacher;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\ClassesService;
use Yii;
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月5日 下午2:18:54 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 
 class ClassesController extends ApiBaseController{
     
     public function actionList() {
         $data = Yii::$app->getRequest()->get();
         $page = $data['page'] ?? 1 ;
         $pageSize = $data['limit'] ?? 10;
         $list = ClassesService::getClassList($page, $pageSize);
         return $this->success($list);
     }
     
     public function actionCreate() {
         $data = Yii::$app->getRequest()->post();
         $rules = [
             [['number', 'name', 'school_id'], 'required'],
             [['school_id'], 'integer'],
             [['number', 'name'], 'string'],
         ];
         $type = $data['type'] ?? 1;
         $comments = $data['comments'] ?? '';
         $this->validate($data, $rules);
         $return = ClassesService::addClass($data['school_id'], $data['number'], $data['name'], $type, $comments);
         return $this->success($return);
     }
     
     public function actionFindone() {
         $data = Yii::$app->getRequest()->get();
         $rules = [
             [['id'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $class = ClassesService::findOne($data['id']);
         return $this->success($class);
     }
     
     public function actionUpdate() {
         $data = Yii::$app->getRequest()->post();
         unset($data['token']);
         $rules = [
             [['id'], 'required'],
             [['id'], 'integer'],
             [['number', 'name'], 'string'],
         ];
         $this->validate($data, $rules);
         $return = ClassesService::updateClass($data);
         if (isset($return['is_error'])) {
             return $this->apiResult(0, $return['message']);
         }
         return $this->success($return);
     }
     
     public function actionChangelesson() {
         $data = Yii::$app->getRequest()->post();
         unset($data['token']);
         $rules = [
             [['id', 'lesson_id'], 'required'],
             [['id'], 'integer'],
             [['lesson_id'], 'string'],
         ];
         $this->validate($data, $rules);
         $return = ClassesService::updateClass($data);
         if (isset($return['is_error'])) {
             return $this->apiResult(0, $return['message']);
         }
         return $this->success($return);
     }
     
     public function actionAddstudent() {
         $data = Yii::$app->getRequest()->post();
         unset($data['token']);
         $rules = [
             [['id', 'student_ids'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $student_ids = $data['student_ids'];
         if (!is_array($student_ids)) {
             $student_ids = json_decode($student_ids, true);
         }
         $return = ClassesService::addStudent($data['id'], $student_ids);
         if (!isset($return['is_error'])) {
             return $this->success($return);
         }
         return $this->apiResult(0, $return['message']);
     }
     
     public function actionRemovestudent() {
         $data = Yii::$app->getRequest()->post();
         unset($data['token']);
         $rules = [
             [['id', 'student_ids'], 'required'],
             [['id'], 'integer'],
         ];
         $this->validate($data, $rules);
         $student_ids = $data['student_ids'];
         if (!is_array($student_ids)) {
             $student_ids = json_decode($student_ids, true);
         }
         $return = ClassesService::removeStudent($data['id'], $student_ids);
         if (!isset($return['is_error'])) {
             return $this->success($return);
         }
         return $this->apiResult(0, $return['message']);
     }
 }
 
 