<?php

namespace Admin\modules\feedback\controllers;
use Admin\service\FeedbackService;
use Yii;
use Admin\modules\AdminController;
use yii\data\Pagination;
use common\models\utils\FuncUtil;
use yii\helpers\Url;


class FeedbackController extends AdminController
{
    const PAGE_PER_NUMBER = 10;      // 15


    /**
     * 用户管理默认显示页面
     */
    public function actionIndex()
    {
        return $this->render('index.twig');
    }

    /**
     * ajax 展示意见反馈数据
     * @return string
     */
    public function actionIndexList()
    {
        if(\Yii::$app->request->isAjax){
            $get = FuncUtil::parseData(Yii::$app->request->get());
            $pageno = !empty($get['page']) ? $get['page'] - 1 : 0;
            $where = [];
            $order = '';
            if (!empty($get['uid'])) {
                $where[] = ['uid' => $get['uid']];
            }

            $lists = FeedbackService::getList($pageno, self::PAGE_PER_NUMBER, $where, $order);
            $count = FeedbackService::getCount($where);
            $pi = new Pagination(['totalCount' => $count, 'pageSize' => self::PAGE_PER_NUMBER]);
            $pi -> route = '/feedback/feedback/index-list';
            $renderData = [
                'search' => $get,
                'lists' => $lists,
                'count' => $count,
                'pagenum' => $pi->pageCount,
                'pagination' => $pi,
            ];
            return $this->render('ajaxfeedback.twig', $renderData);
        }
    }

    public function actionForbid()
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax){
            $id = Yii::$app->request->get('id');
            if($id){
                TaskService::dosetStatus($id,0);
                $response->data = ['code'=>200,'message'=>'操作成功','url'=>'/task/task/index'];
            }
        }
    }

    public function actionActive()
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax){
            $id = Yii::$app->request->get('id');
            if($id){
                TaskService::dosetStatus($id,1);
                $response->data = ['code'=>200,'message'=>'操作成功','url'=>'/task/task/index'];
            }
        }
    }
}