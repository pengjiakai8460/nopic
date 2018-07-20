<?php

namespace Api\modules\v1\controllers\task;


use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\TaskService;
use Api\services\v1\UserService;
use common\models\orm\XmReportExam;
use common\models\orm\XmReportTask;
use common\models\orm\XmReportTaskDetail;

class IndexController extends ApiBaseController
{
    /**
     * 每日任务数据api接口
     * @return array
     */
    public function actionList()
    {
        $get = \Yii::$app->request->get();
        if(isset($get['r_id']) && $get['r_id']){
            $rules = [
                [['r_id'], 'required'],
                [['r_id'], 'integer'],
            ];
            $this->validate($get, $rules);
            $data = TaskService::getHistoryDetail($get);
        }else{
            $data = TaskService::showTodayList();
        }
        return $this->success($data);
    }

    /**
     * 每日任务提交api接口
     * @return array
     */
    public function actionSave()
    {
        $post = \Yii::$app->request->post();
        if($post){
            $taskInfo = TaskService::getDayTaskInfo();
            if(isset($taskInfo['id'])){
                $post['tid'] = $taskInfo['id'];
                $post['score'] = $taskInfo['all_score'];
                $res = TaskService::doSaveReportTask($post);
                return $this->result($res['code'],$res['message'],[]);
            }
        }
    }




    public function actionDelete()
    {
        $time = time();
        $date = date('Y-m-d',$time);
        $startT = strtotime($date);
        $endT = $startT + 24 * 3600 -1;
        $uid = UserService::$userInfo['uid'];
        $taskInfo = XmReportTask::find()->where(['user_id'=>$uid])->andWhere(['>=','add_time',$startT])->andWhere(['<','add_time',$endT])->one();
        if($taskInfo){
            XmReportTaskDetail::deleteAll(['report_task_id'=>($taskInfo->id),'user_id'=>$uid]);
            $taskInfo ->delete();
        }
        return $this->success();
    }


    public function actionHistory()
    {
        $get = \Yii::$app->request->get();
        if($get){
            $rules = [
                [['d'], 'required'],
                [['d'], 'integer'],
                [['tt'], 'string'],
            ];
            $this->validate($get, $rules);
            $data = TaskService::getHistory($get);
            return $this->success($data);
        }
    }

}