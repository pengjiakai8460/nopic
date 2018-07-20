<?php
namespace SLApi\modules\v1\controllers\admin;

use Yii;
use SLApi\modules\v1\controllers\ApiBaseController;
use SLApi\services\v1\LessonService;
/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月13日 下午2:29:05 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
class LessonController extends ApiBaseController {
    
    public $modelClass  = 'common\models\orm\XmBLesson';
    
    public function actions() {
        $actions = parent::actions();
        unset($actions['update'], $actions['create'], $actions['delete']);
        return $actions;
    }
    
    public function actionCreate() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['course_id', 'title'], 'required'],
            [['course_id'], 'integer'],
            [['title', 'summary', 'image'], 'string'],
        ];
        $this->validate($data, $rules);
        $summary = $data['summary'] ?? '';
        $image = $data['image'] ?? '';
        $section = $data['section'] ?? [];
        if (!empty($section)) {
            $section = json_decode($section, true);
        }
        $return = LessonService::addLesson(trim($data['course_id']), trim($data['title']), trim($summary), trim($image), $section);
        return $this->success($return);
    }
    
    public function actionFindone() {
        $data = Yii::$app->getRequest()->get();
        $rules = [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
        $this->validate($data, $rules);
        $return = LessonService::findOne(trim($data['id']));
        return $this->success($return);
    }
    
    public function actionList() {
        $data = Yii::$app->getRequest()->get();
        $page = $data['page'] ?? 1 ;
        $pageSize = $data['limit'] ?? 10;
        $list = LessonService::getLessonList($page, $pageSize);
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
            [['id', 'course_id'], 'integer'],
            [['title', 'summary'], 'string'],
        ];
        $this->validate($data, $rules);
        $return = LessonService::updateLesson($data);
        return $this->success($return);
    }
    
    public function actionDelete() {
        $data = Yii::$app->getRequest()->get();
        $rules = [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
        $this->validate($data, $rules);
        $return = LessonService::deleteLesson(trim($data['id']));
        return $this->success($return);
    }
    
}



 
 
 