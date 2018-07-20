<?php
namespace SLApi\modules\v1\controllers\student;

use Yii;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\CourseService;
use SLApi\services\v1\LessonService;
            /**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月11日 下午8:08:59 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
 class CourseController extends ApiBaseController {
     
     public $modelClass  = 'common\models\orm\XmBCourse';
     
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
         $return = CourseService::findOne(trim($data['id']));
         return $this->success($return);
     }
     
     public function actionList() {
         $data = Yii::$app->getRequest()->get();
         $page = $data['page'] ?? 1 ;
         $pageSize = $data['limit'] ?? 10;
         $list = CourseService::getCourseList($page, $pageSize);
         
         return $this->success($list);
     }

 }
 
 
 