<?php

namespace Api\services\v1;

use common\models\orm\XmCTag;
use common\models\orm\XmCUserDetail;
use common\models\orm\XmReportTaskDetail;
use common\models\orm\XmUser;
use Yii;
use common\base\BaseService;
use common\models\orm\XmCExam;
use common\models\orm\XmCExamTestlets;
use common\models\orm\XmCTestletsQuestion;
use common\models\orm\XmCQuestion;
use common\models\orm\XmCQuestionTags;
use common\models\orm\XmReportTask;
use common\models\orm\XmUsers;

/**
 * Content 每日任务管理
 */
class TaskService extends BaseService
{

    const imgDomain = 'http://oss.xiaoma.wang/';
    //http://oss.xiaoma.wang/Public/Scratch/Scrachxin/image/xiaoma.png
    const defaultAvatarImg = 'http://xmyj.oss-cn-shanghai.aliyuncs.com/Uploads/xmsj/front/img/b-icon.png';

    public static function getDayTaskInfo()
    {

        $benginTime = strtotime(date('Y-m-d', time())) - 8 * 3600;

        $endTime = $benginTime  + 24 * 3600 - 1;

        $taskInfo = XmCExam::find()
            ->where(['type' => 3])
            ->andWhere(['status' => 1])
            ->andWhere(['>=', 'task_date', $benginTime])
            ->andWhere(['<', 'task_date', $endTime])
            ->select('id,title,all_times,all_score')
            ->asArray()
            ->one();
        return $taskInfo;
    }


    public static function showTodayList()
    {
        $taskInfo = self::getDayTaskInfo();
        return self::taskList($taskInfo);
    }


    /**
     * 返回每日任务数据
     * @param $taskInfo
     * @return mixed
     */
    public static function taskList($taskInfo)
    {

        //获取 我的任务记录
        $myTaskInfo = self::getMyReportTask(isset($taskInfo['id']) ? $taskInfo['id'] : -1);

        if ($taskInfo) {

            $taskInfo['report'] = $myTaskInfo ? true : false;// 判断是去做每日任务 还是查看每日任务

            //获取每日任务的问题详情
            $testletsIdArr = XmCExamTestlets::find()->where(['e_id' => $taskInfo['id']])->select('t_id')->asArray()->all();
            $testletsIdArr = $testletsIdArr ? array_column($testletsIdArr, 't_id') : -1;
            $qidSortsArr = XmCTestletsQuestion::find()->where(['in', 't_id', $testletsIdArr])->select('q_id,sort')->asArray()->all();
            $qidsArr = $qidSortsArr ? array_column($qidSortsArr, 'q_id') : -1;
            $sortsArr = $qidSortsArr ? array_column($qidSortsArr, 'sort', 'q_id') : '';
            $qusetionData = XmCQuestion::find()->where(['in', 'id', $qidsArr])->select('id,title,content,explain,type')->asArray()->all();

            //显示每日任务问题
            if (!$taskInfo['report']) {
                $qusetionData = self::formatDoTaskData($qusetionData, $sortsArr);
            }

            //查看每日任务答案
            if ($taskInfo['report']) {
                //获取自己做的每日任务 记录
                $myTaskInfo = XmReportTaskDetail::find()->where(['report_task_id'=>$myTaskInfo['id']])->asArray()->all();

                $myQusAnswer = $myTaskInfo ? array_column($myTaskInfo, 'answer', 'q_id') : [];
                $qusetionData = self::formatReportTaskData($qusetionData, $qidsArr, $sortsArr, $myQusAnswer);
            }
            array_multisort(array_column($qusetionData, 'sort'), SORT_ASC, $qusetionData);
            $taskInfo['questions'] = $qusetionData;

            //返回用户完成情况
            $taskInfo['users'] = self::formTaskUserData($taskInfo['id']);
        }
        return $taskInfo;
    }

    /**
     * 每日任务已完成 数据处理
     * @param $qusetionData
     * @param $qidsArr
     * @param $sortsArr
     * @param $myQusAnswer
     * @return mixed
     */
    public static function formatReportTaskData($qusetionData, $qidsArr, $sortsArr, $myQusAnswer)
    {
        //获取问题与知识点的关系
        $qidTagIdData = XmCQuestionTags::find()->where(['in', 'q_id', $qidsArr])->andWhere(['status' => 1])->select('q_id,tag_id')->asArray()->all();
        $qIdTagDataArr = [];
        //得到 知识点的 名称与id
        if ($qidTagIdData) {
            $tagIds = isset($qidTagIdData) ? array_unique(array_column($qidTagIdData, 'tag_id')) : [-1];
            $tagData = XmCTag::find()->where(['in', 'id', $tagIds])->select('id,name')->asArray()->all();
            $tagIdNameArr = isset($tagData) ? array_column($tagData, 'name', 'id') : [];
            //得到
            foreach ($qidTagIdData as $k => $v) {
                $temp = [
                    'tag_id' => $v['tag_id'],
                    'tag_name' => $tagIdNameArr[$v['tag_id']]
                ];
                $qIdTagDataArr[$v['q_id']][] = $temp;
            }
        }

        //将我的选择的内容 知识点标签 加入到 题目Data中
        foreach ($qusetionData as $k => $v) {
            $qusetionData[$k]['explain'] = htmlspecialchars_decode(stripcslashes($qusetionData[$k]['explain']));
            $qusetionData[$k]['title'] = htmlspecialchars_decode(stripcslashes($qusetionData[$k]['title']));
            $qusetionData[$k]['sort'] = isset($sortsArr[$v['id']]) ? $sortsArr[$v['id']] : 0;

            $tempCon = json_decode($qusetionData[$k]['content'], true);
            foreach ($tempCon as $tmpkey => $tempvalue) {
                $tempCon[$tmpkey]['c'] = htmlspecialchars_decode(stripcslashes($tempvalue['c']));

                //if($v['qtype'] == 1){
                if($v['type'] == 1){
                    if ($tempCon[$tmpkey]['is_r']) {
                        $qusetionData[$k]['answer'] = [$tempvalue['n']];//获得答案
                    }
                }else{
                    $qusetionData[$k]['answer'][] = $tempvalue['c'];//获得答案
                }

            }
            $qusetionData[$k]['myanswer'] = isset($myQusAnswer[$v['id']]) ? $myQusAnswer[$v['id']] : '';
            $qusetionData[$k]['content'] = $tempCon;
            $qusetionData[$k]['tagData'] = isset($qIdTagDataArr[$v['id']]) ? $qIdTagDataArr[$v['id']] : '';

        }
        return $qusetionData;

    }

    /**
     * 每日任务未完成 数据处理
     * @param $qusetionData
     * @param $sortsArr
     * @return mixed
     */
    public static function formatDoTaskData($qusetionData, $sortsArr)
    {
        foreach ($qusetionData as $k => $v) {
            unset($qusetionData[$k]['explain']);//去掉解析
            $qusetionData[$k]['title'] = htmlspecialchars_decode(stripcslashes($qusetionData[$k]['title']));
            $qusetionData[$k]['sort'] = $sortsArr[$v['id']];
            $qusetionData[$k]['type'] = $v['type'];

            $tempCon = json_decode($qusetionData[$k]['content'], true);
            foreach ($tempCon as $tmpkey => $tempvalue) {
                $tempCon[$tmpkey]['c'] = htmlspecialchars_decode(stripcslashes($tempvalue['c']));
                unset($tempCon[$tmpkey]['is_r']);//去掉答案
            }
            $qusetionData[$k]['content'] = $tempCon;
        }
        return $qusetionData;
    }

    /**
     * 每日任务 用户完成情况
     * @param $taskId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function formTaskUserData($taskId)
    {
        $users = [];
        $userIds = (new \yii\db\Query())
            ->select(['user_id'])
            ->from('xm_report_task')
            ->where(['e_id' => $taskId])
            ->all();

        if($userIds){
            $userIds = array_column($userIds,'user_id');
            $users = XmUsers::find()->where(['in','id',$userIds])->select('id,nickname,name,avatar_img')->asArray()->all();
            foreach ($users as $k=>$v){
                if( !$users[$k]['avatar_img'] ){
                    $users[$k]['avatar_img'] = self::defaultAvatarImg;
                }else{
                    $avatarImgInfo = json_decode($users[$k]['avatar_img']);
                    $users[$k]['avatar_img'] = self::imgDomain . $avatarImgInfo['name'];
                }
            }
        }
        return $users;
    }

    /**
     * 返回我的任务记录
     * @param $taskId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMyReportTask($taskId)
    {
        return XmReportTask::find()->where(['user_id' => UserService::$userInfo['uid']])->andWhere(['e_id' => $taskId])->select('id,e_id')->asArray()->one();
    }


    /**
     * 每日任务统计
     * @param $post
     * @return array
     * @throws \yii\db\Exception
     */
    public static function doSaveReportTask($post)
    {
        $res = ['code' => 200, 'message' => 'success'];
        $time = time();
        $taskId = isset($post['tid']) ? $post['tid'] : '';
        $answer = isset($post['answer']) ? json_decode($post['answer'], true) : [];
        $uid = UserService::$userInfo['uid'];
        $reportDetailInfo = XmReportTask::find()->where(['user_id' => $uid])->andWhere(['e_id' => $taskId])->asArray()->all();

        if ($reportDetailInfo) {
            $res['code'] = 403;
            $res['message'] = '已经完成了';
            return $res;
        } else {
            //添加任务记录
            $tastRM = new XmReportTask();
            $tastRM -> add_time = $time;
            $tastRM -> user_id = $uid;
            $tastRM -> e_id  = $taskId;
            $tastRM -> is_complete   = 1;
            $tastRM -> update_time = $time;
            $tastRM -> save();

            $insertData = [];
            foreach ($answer as $k => $v) {
                $temp = [
                    'report_task_id'=>$tastRM->id,
                    'user_id' => $uid,
                    'e_id' => $taskId,
                    'q_id' => $k,
                    'answer' => $v,
                    'status' => 1,
                    'add_time' => $time,
                    'update_time' => $time,
                ];
                $insertData[] = $temp;
            }
            if($insertData){
                \Yii::$app->db->createCommand()->batchInsert(XmReportTaskDetail::tableName(), ['report_task_id','user_id', 'e_id', 'q_id', 'answer', 'status', 'add_time', 'update_time'], $insertData)->execute();
            }

            //添加任务做题天数
            $userInfo = XmCUserDetail::find()->where(['user_id'=>$uid])->one();
            if(($time - $userInfo->last_task_time) < 86400){
                $userInfo -> task_day = $userInfo -> task_day + 1;
                $userInfo-> last_task_time = $time;
            }else{
                $userInfo-> last_task_time = $time;
                $userInfo -> task_day = 1;
            }
            $userInfo -> save();


            return $res;
        }

    }

    public static function getHistory($post)
    {
        $res = ['history'=>[],'task_day'=>0];

        $uid = UserService::$userInfo['uid'];
        $taskDay = XmCUserDetail::find()->where(['user_id'=>$uid])->select('task_day')->asArray()->one();
        $res['task_day'] = $taskDay['task_day'] ?? 0 ;

        $post['tt'] = isset($post['tt']) ? $post['tt'] : '';
        switch ($post['tt']){
            case 'day':
                $beginTime = strtotime($post['d']);
                $endTime = $beginTime  + 86400 -1;
                break;
            case 'month':
                $beginTime = strtotime($post['d']);
                $monthDay = date("t",strtotime($post['d']));
                $endTime = $beginTime  + 86400 * $monthDay -1;
                break;
            default:
                $beginTime = strtotime(date("Y-m-d"),time());
                $endTime = $beginTime  + 86400 -1;
                break;
        }

        $reportData = XmReportTask::find()->where(['user_id'=>$uid])->andWhere(['is_complete'=>1])->andWhere(['>=','add_time',$beginTime])->andWhere(['<','add_time',$endTime])->select('id as r_id,add_time')->asArray()->all();
        foreach ($reportData as $k=>$v){
            $reportData[$k]['add_time'] = date('Y-m-d',$v['add_time']);
        }
        $res['history'] = $reportData ?? [] ;
        return $res;
    }

    public static function getHistoryDetail($post)
    {
        $res = ['historydetail'=>[],'task_day'=>0];
        $uid = UserService::$userInfo['uid'];

        $taskDay = XmCUserDetail::find()->where(['user_id'=>$uid])->select('task_day')->asArray()->one();
        $res['task_day'] = $taskDay['task_day'] ?? 0 ;

        $post['r_id'] = isset($post['r_id']) ? $post['r_id'] : -1;
        $reportInfo = XmReportTask::find()->where(['id'=>$post['r_id']])->select('id,e_id')->asArray()->one();
        $taskInfo = [];
        if($reportInfo){
            $taskInfo = XmCExam::find()->where(['id'=>$reportInfo['e_id']])->select('id,title,all_times,all_score')->asArray()->one();

            if($taskInfo){
                return self::taskList($taskInfo);
            }
        }
        $res['historydetail'] = $taskInfo;
        return $res;
    }
}