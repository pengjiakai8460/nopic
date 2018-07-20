<?php
namespace Api\modules\v1\controllers\exam;
use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\ExamService;
use Api\services\v1\UserService;
use common\models\User;

class IndexController extends ApiBaseController
{
    /**
     * @apiDefine Exam
     * 套题列表
     *
     */

    //套卷列表
    /**
     * @api {get} exam/index/index  Index Index
     * @apiVersion 1.0.0
     * @apiName Index Index
     * @apiGroup Examsvn
     *
     * @apiSuccess {String} type 试卷类型 1:真题 2:模拟题（默认为1）
     * @apiSuccess {String} offset
     * @apiSuccess {String} limit
     */
    public function actionIndex()
    {
        $userInfo = UserService::$userInfo;
        $type = \Yii::$app->request->get('type', 1);
        $id = \Yii::$app->request->get('id', 0);
        $limit = \Yii::$app->request->get('limit');
        $list = ExamService::examList($userInfo['uid'],$type, $id, $limit);
        $ret = array();
        $ret['list'] = $list;
        return $this->result(200,'成功',$list);
    }

    //根据用户行为生成或者恢复相应的套卷做题记录
    public function actionCreateReport()
    {
        $userInfo = UserService::$userInfo;
        $get = \Yii::$app->request->get();
        $actionStatus = empty($get['status']) ? 0 : $get['status'];
        $examId = $get['exam_id'];
        $report_id = ExamService::createReportExam($userInfo['uid'], $examId, $actionStatus);
        if($report_id == 0){
            return $this->result(300, '铛！恭喜你出错了！');
        }
        return $this->result(200, '成功', ['report_id'=>$report_id]);
    }

    //获取相应的做题记录的试卷内容
    public function actionReportExamDetail()
    {
        $get = \Yii::$app->request->get();
        $report_id = $get['report_id'];
        if(!ExamService::userIsIdentical($report_id)){
            return $this->result(300, '查看非本人记录，无权查看');
        }
        $examDetail = ExamService::reportExamCompleteInformation($report_id);
        $examDetail['report_id'] = $report_id;
        return $this->result(200, '成功', $examDetail);
    }

    //保存单题的做题记录
    public function actionSaveReportQuestion()
    {
        $userInfo = UserService::$userInfo;
        $post = \Yii::$app->request->post();
        $user_id = $userInfo['uid'];
        $report_id = $post['report_id'];
        $exam_id = $post['exam_id'];
        $t_id = $post['t_id'];
        $q_id = $post['q_id'];
        if(!ExamService::userIsIdentical($report_id)){
            return $this->result(302, '查看的记录非本人记录，无权查看');
        }
        if(!ExamService::isAllowChangeReport($report_id)){
            return $this->result(301, '做题记录已经提交，无法做出更改！');
        }
        $answer = json_decode($post['answer']);
        $ret = ExamService::saveReportExamQuestion($report_id, $user_id,$exam_id, $t_id, $q_id, $answer);
//        $ret = ExamService::saveReportExamQuestion(2590, 3133,3, 1, 1   , ["D"]);
        if($ret > 0){
            return $this->result(200, '成功');
        }else{
            return $this->result(300, '变更失败');
        }
    }

    //提交保存整张试卷(考试交卷)
    public function actionSaveReportExam()
    {
        $userInfo = UserService::$userInfo;
        $times = \Yii::$app->request->post('times', 0);
//        $complete_state = $get['complete_state'];
        $report_id = \Yii::$app->request->post('report_id');
        if(!ExamService::userIsIdentical($report_id)){
            return $this->result(300,  '非本人记录不可操作！');
        }
        //保存提交数据
        $ret = ExamService::saveReportExam($userInfo['uid'],$report_id, $times);
        if($ret){
            return $this->result(200,  '成功');
        }else{
            return $this->result(301, '试卷已经提交，无法再次提交！');
        }

    }

    //练习记录列表
    public function actionPracticeRecord()
    {
        $userInfo = UserService::$userInfo;
        $page = \Yii::$app->request->get('page', 1);
        $limit = \Yii::$app->request->get('limit', 10);
        $type = \Yii::$app->request->get('type', 0);
        $start_time = \Yii::$app->request->get('start_time', 0);
        $end_time = \Yii::$app->request->get('end_time', 0);
        $is_accept = \Yii::$app->request->get('is_accept', 2);
        $record = ExamService::practiceRecord($userInfo['uid'], $type, $is_accept, $start_time, $end_time, $page, $limit);
        if(!empty($record['list'])){
            foreach ($record['list'] as $key=>$value) {
                $time = $value['times'];
                $hour = floor($time/3600);
                if($hour < 10){
                    $hour = '0'.$hour;
                }
                $minute = floor(($time - 3600 * $hour)/60);
                if($minute < 10){
                    $minute = '0'.$minute;
                }
                $second = $time - 3600 * $hour - 60 * $minute;
                if($second < 10){
                    $second = '0'.$second;
                }
                $record['list'][$key]['times'] = $hour.':'.$minute.':'.$second;
            }
        }
        return $this->result(200, '成功', $record);
    }

    //练习报告
    public function actionPracticePresentation()
    {
        $report_id = \Yii::$app->request->get('report_id');
//        $report_id = 1086;
        if(!ExamService::userIsIdentical($report_id)){
            return $this->result(300, '查看的记录非本人记录，无权查看');
        }
        $ret = ExamService::practicePresentation($report_id);
        if($ret == 0){
            return $this->result(214, '未提交的套题！无法生成报告');
        }
        return $this->result(200, '成功', $ret);
    }


    //记录做题时间
    public function actionSaveConsumeTimes()
    {
        $report_id = \Yii::$app->request->get('report_id');
        $times = \Yii::$app->request->get('times');
        $ret = ExamService::saveConsumeTimes($report_id, $times);
        if($ret == 'fail'){
            return $this->result(301, '当前做题记录已经提交，无法做出记录更改');
        }else{
            return $this->result(200, '成功');
        }
    }
}