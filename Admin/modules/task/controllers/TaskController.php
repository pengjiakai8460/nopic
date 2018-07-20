<?php

namespace Admin\modules\task\controllers;


use common\models\orm\XmCQuestion;
use Yii;
use Admin\modules\AdminController;
use yii\data\Pagination;
use common\models\utils\FuncUtil;
use Admin\service\TaskService;
use Admin\service\TagService;
use yii\helpers\Url;


class TaskController extends AdminController
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
     * ajax 展示每日任务数据
     * @return string
     */
    public function actionIndexList()
    {
        if(\Yii::$app->request->isAjax){
            $get = FuncUtil::parseData(Yii::$app->request->get());
            $pageno = !empty($get['page']) ? $get['page'] - 1 : 0;
            $where = [];
            $order = '';
            if (!empty($get['task_name'])) {
                $where[] = ['like', 'title', $get['task_name']];
            }

            if (!empty($get['task_id'])) {
                $where[] = ['id' => $get['task_id']];
            }

            $lists = TaskService::getList($pageno, self::PAGE_PER_NUMBER, $where, $order);
            $count = TaskService::getCount($where);
            $pi = new Pagination(['totalCount' => $count, 'pageSize' => self::PAGE_PER_NUMBER]);
            $pi -> route = '/task/task/index-list';
            $renderData = [
                'search' => $get,
                'lists' => $lists,
                'count' => $count,
                'pagenum' => $pi->pageCount,
                'pagination' => $pi,
            ];
            return $this->render('ajaxtask.twig', $renderData);
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

    /**
     * 每日任务添加页面
     * @return string
     */
    public function actionAdd()
    {
        $renderData['task'] = [
            'id'=>'',
            'title'=>''
        ];
        $renderData['qids'] = '';
        $renderData['qdatas'] = [];
        $renderData['postUrl'] = Url::to(['task/dosave']);
        return $this->render('add.twig', $renderData);
    }

    /**
     * 每日任务编辑页面
     * @return string
     */
    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');

        $renderData['task'] = TaskService::getExamDetail($id);
        if ($renderData['task']) {
            $renderData['task']['task_date'] = date('Y-m-d',$renderData['task']['task_date']);
            $renderData['qdatas'] = TaskService::getTestletsDetailByTaskId($id);
            $renderData['qids'] = $renderData['qdatas']?implode(',',array_column($renderData['qdatas'],'id')):'';
        }

        $renderData['postUrl'] = Url::to(['task/dosave']);
        return $this->render('edit.twig', $renderData);
    }


    /**
     * 加载问题选择模态框页面
     * @return string
     */
    public function actionQadd()
    {
        $get = FuncUtil::parseData(Yii::$app->request->get());
        $taskId = !empty($get['task_id']) ? $get['task_id'] : 0;
        $qids = Yii::$app->request->get('qids');

        $renderData['tags'] = json_encode(TagService::getAllLabels(),true);
        $renderData['qcount'] = TaskService::getTaskQuestionCount($taskId);
        $renderData['qids'] = $qids;

        return $this->render('qadd.twig', $renderData);
    }

    /**
     * 问题选择模态框 搜索分页展示
     * @return string
     */
    public function actionQajax()
    {
        if (Yii::$app->request->isAjax) {
            $get = FuncUtil::parseData(Yii::$app->request->get());
            $qids = !empty($get['qids']) ? explode(',',htmlspecialchars_decode($get['qids'])) :[-1];
            $examId= !empty($get['e_id']) ? $get['e_id']  : 0;
            $pageno = !empty($get['page']) ? $get['page'] - 1 : 0;
            $tagId = !empty($get['tag_id']) ? $get['tag_id'] : 0;
            $type = !empty($get['type']) ? $get['type'] : 0;
            $searchkey = !empty($get['searchkey']) ? $get['searchkey'] : '';
            $searchqtype = !empty($get['searchqtype']) ? $get['searchqtype'] : '';
            $stringQids = implode(',',$qids);
            $orderBy = ["field( id, {$stringQids} )"=>true];
            $searchParam = [
                'exam_id'=>$examId,
                'tag_id'=>$tagId,
                'type'=>$type,
                'searchkey'=>$searchkey,
                'searchqtype'=>$searchqtype,
                'pageno'=>$pageno,
                'pagelength'=>3,
                'order'=> $orderBy,
                'q_id'=>$qids,
            ];
            $questionData = TaskService::searchQuestion($searchParam);

            $renderData['qids'] = $qids;
            $pi = new Pagination(['totalCount' => $questionData['count'], 'pageSize' => 3]);
            $pi -> route = '/task/task/qajax';
            $renderData['search'] = $get;
            $renderData['count'] = $questionData['count'];
            $renderData['lists'] = $questionData['list'];
            $renderData['pagenum'] = $pi->pageCount;
            $renderData['pagination'] = $pi;
            return $this->render('qform.twig', $renderData);
        }
    }


    public function actionDosave()
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        if($request->isPost){
            $addLabelData = $request->post();
            $data = TaskService::dosave($addLabelData);
            $returnUrl = '/task/task/edit?id='.$data['task_id'];
            if($data){
                $response->data = ['code'=>200,'message'=>'操作成功','url'=>$returnUrl];
            }
        }else{
            $response->data = ['code'=>400,'message'=>'操作失败'];
        }
    }

}