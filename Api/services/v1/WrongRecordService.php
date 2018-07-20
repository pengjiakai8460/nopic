<?php

namespace Api\services\v1;

use Codeception\Module\Redis;
use common\models\orm\XmCConfig;
use common\models\orm\XmCTag;
use common\models\orm\XmReportExam;
use common\models\orm\XmReportUserData;
use common\models\orm\XmUser;
use Yii;
use common\base\BaseService;
use common\models\orm\XmCExam;
use common\models\orm\XmCExamTestlets;
use common\models\orm\XmCTestletsQuestion;
use common\models\orm\XmCQuestion;
use common\models\orm\XmCQuestionTags;
use common\models\orm\XmUsers;
use common\models\orm\XmReportExamQuestion;
use common\models\orm\XmReportErrorQuestion;
use common\models\orm\XmUserRate;


/**
 * Content 每日任务管理
 */
class WrongRecordService extends BaseService
{

    const imgDomain = 'http://oss.xiaoma.wang/';

    const defaultAvatarImg = 'http://oss.xiaoma.wang/Public/Scratch/Scrachxin/image/xiaoma.png';


    /**
     * 错题页面数据
     * @param $get
     * @return array
     */
    public static function wrongrecordList($get)
    {
        $uid = UserService::$userInfo['uid'];

        $xmErrorQuesM = XmReportErrorQuestion::find()->where(['user_id'=>$uid]);
        //下来刷新 查询
        if(isset($get['r_id']) && $get['r_id'] != 0){
            $xmErrorQuesM -> andWhere(['<','id',$get['r_id']]);
        }
        if(isset($get['type']) && $get['type'] != 0){
            $qids = XmCQuestion::find()->andWhere(['type'=>$get['type']])->select('id')->asArray()->all();
            $qids = $qids ? array_column($qids,'id') : [-1];
            $xmErrorQuesM -> andWhere(['in','q_id',$qids]);
        }
        if(isset($get['errtype']) && $get['errtype'] != 0){
            $xmErrorQuesM -> andWhere(['error_type'=>$get['errtype']]);
        }

        if(isset($get['errcount']) && $get['errcount'] != 0){
            if($get['errcount'] >= 3){
                $xmErrorQuesM -> andWhere(['>=','error_count',$get['errcount']]);
            }else{
                $xmErrorQuesM -> andWhere(['error_count'=>$get['errcount']]);
            }
        }
        $xmErrorQuesM -> orderBy('id desc')->limit(5);
        //获取错误记录
        $reportData = $xmErrorQuesM ->asArray()->all();

        //得到问题id
        $qidArr = array_column($reportData,'q_id');

        //获取自己做的问题内容详情
        $reportExamData = XmReportExamQuestion::find()->where(['user_id'=>$uid])->andWhere(['in','q_id',$qidArr])->andWhere(['is_accept'=>1])->andWhere(['is_right'=>0])->select('id,q_id,answer,user_id,exam_id')->asArray()->all();


        //获取问题内容
        $questionData = XmCQuestion::find()->where(['in','id',$qidArr])->select('id,title,content,explain,type as type')->asArray()->all();

        //format问题内容 转义 知识点标签
        $myAnswer = array_column($reportExamData,'answer','q_id');
        //以q_id为key 做为一个新的数组
        $valueQidArr = [];
        foreach ($reportData as $k=>$v){
            $v['r_id'] = $v['id'];
            $valueQidArr[$v['q_id']] = $v;
        }
        $errorQuesData = TaskService::formatReportTaskData($questionData, $qidArr, array_column($valueQidArr,'r_id','q_id'), $myAnswer);
        array_multisort(array_column($errorQuesData,'sort'),SORT_DESC,$errorQuesData);

        //得到对应问题 所属的 试卷id  自己回答的内容
        $examIdQid = [];
        $examIdArr = [];
        if($reportExamData){
            foreach ($reportExamData as $row){
                $examIdQid[$row['q_id']]['exam'][] = $row['exam_id'];
                $examIdQid[$row['q_id']]['answer'][] = $row['exam_id'];
                $examIdArr[] =  $row['exam_id'];
            }
        }

        //得到试卷的id 与标题
        $examData = XmCExam::find()->where(['in','id',$examIdArr])->select('id,title')->asArray()->all();
        $examIdNameArr = $examData ? array_column($examData,'title','id') : [];
        if($errorQuesData){
            foreach ($errorQuesData as $k=>$v){
                if(isset($examIdQid[$v['id']])){
                    foreach ($examIdQid[$v['id']]['exam'] as $key=>$val){
                        $errorQuesData[$k]['exam_title'][] = $examIdNameArr[$val];
                    }
                }
                //设置解析
                $errorQuesData[$k]['remark'] = isset($valueQidArr[$v['id']]['remark']) ? $valueQidArr[$v['id']]['remark'] : '';
                //设置错误次数
                $errorQuesData[$k]['error_count'] = isset($valueQidArr[$v['id']]['error_count']) ? $valueQidArr[$v['id']]['error_count'] : '';
                //设置错误类型
                $errorQuesData[$k]['error_type'] = isset($valueQidArr[$v['id']]['error_type']) ? $valueQidArr[$v['id']]['error_type'] : '';
                //设置r_id
                $errorQuesData[$k]['r_id'] = isset($valueQidArr[$v['id']]['r_id']) ? $valueQidArr[$v['id']]['r_id'] : '';
            }
        }


        return ['wrong_record'=>$errorQuesData];
    }


    /**
     * 得到当前用户错题的统计信息
     * @return array
     */
    public static function getWrongCalCu()
    {
        $uid = UserService::$userInfo['uid'];

        $tjData = self::getRecordRedis($uid);
        //获取正确率
        $userRateData = XmUserRate::find()->where(['uid'=>$uid])->asArray()->one();
        $userInfo['correct_rate'] = 0;
        if($userRateData){
            $userInfo['correct_rate'] = ($userRateData['type1_right'] + $userRateData['type2_right'] +
                    $userRateData['type3_right'] + $userRateData['type4_right'])/($userRateData['type1_all'] + $userRateData['type2_all'] + $userRateData['type3_all'] + $userRateData['type4_all']);
            $userInfo['correct_rate'] = sprintf("%2d",$userInfo['correct_rate'] * 100) ;
        }
        //获取平均正确率
        $averageRate = XmCConfig::find()->where(['key'=>'average_rate'])->select('value')->asArray()->one();
        $userInfo['average_rate'] = $averageRate ? $averageRate['value'] : 0;
        return array_merge($tjData,$userInfo);
    }

    /**
     * 得到错题题型 错误知识点分布
     * @param $uid
     * @return array
     */
    public static function getRecordRedis($uid)
    {
        $tagData = XmCTag::find()->where(['pid'=>0])->select('id,name')->asArray()->all();
        $recordInfo = RedisService::getHash('wrong_record_uid_'.$uid);

        foreach ($tagData as  $k=>$v){
            if(isset($recordInfo['tag_'.$v['id']])){
                $tagData[$k]['wrong_count'] = $recordInfo['tag_'.$v['id']];
            }else{
                $tagData[$k]['wrong_count'] = 0;
            }
        }

        $qTypeData = [
            ['type'=>1,'name'=>'单选选择','wrong_count'=> isset($recordInfo['type_1']) ? $recordInfo['type_1'] : 0 ],
            ['type'=>2,'name'=>'问题求解','wrong_count'=> isset($recordInfo['type_2']) ? $recordInfo['type_2'] : 0 ],
            ['type'=>3,'name'=>'阅读程序写结果','wrong_count'=> isset($recordInfo['type_3']) ? $recordInfo['type_3'] : 0 ],
            ['type'=>4,'name'=>'完善程序','wrong_count'=> isset($recordInfo['type_4']) ? $recordInfo['type_4'] : 0 ],
        ];
        return ['wrong_tag'=>$tagData,'wrong_type'=>$qTypeData];
    }

    /**
     * 保存错误题目
     * @param $reportId
     * @return bool
     */
    public static function setWrongRecord( $reportId )
    {
        if($reportId){
            $qidArr = XmReportExamQuestion::find()->where(['report_id'=>$reportId])->andWhere(['is_right'=>0])->select('q_id')->asArray()->all();
            $qid = $qidArr ? array_column($qidArr,'q_id') : [];
        }else{
            return false;
        }
        if(!$qid){
            return false;
        }

        $uid = UserService::$userInfo['uid'];
        self::setRecordRedis($qid, $uid);
        self::setRecordDb($qid, $uid);
        return true;
    }

    /**
     * 设置错误题型分布
     * @param $qid
     * @param $uid
     * @return bool
     */
    public static function setRecordRedis($qid ,$uid)
    {
        //题型分类
        $qType = [
            'type_1'=>[],
            'type_2'=>[],
            'type_3'=>[],
            'type_4'=>[],
        ];
        $questionData = XmCQuestion::find()->where(['in','id',$qid])->select('id,type')->asArray()->all();
        if($questionData){
            foreach ($questionData as $k=>$v){
                array_push($qType['type_'.$v['type']],$v['id'])  ;
            }
        }
        //知识点分布
        $qTag = [];
        $questionTagData = XmCQuestionTags::find()->where(['in','q_id',$qid])->select('q_id,tag_id')->asArray()->all();
        if($questionTagData){
            $tagIdArr = array_column($questionTagData,'tag_id');
            $TagData = XmCTag::find()->where(['in','id',$tagIdArr])->select('top,id')->asArray()->all();
            $topIdArr = array_column($TagData,'top','id');
            foreach ($questionTagData as $k=>$v){
                if(isset($qTag['tag_'.$topIdArr[$v['tag_id']]])){
                    array_push($qTag['tag_'.$topIdArr[$v['tag_id']]],$v['q_id']);
                }else{
                    $qTag['tag_'.$topIdArr[$v['tag_id']]] = [$v['q_id']];
                }
            }
        }
        // saveData
        $recordInfo = RedisService::getHash('wrong_record_uid_'.$uid);

        $saveRedis = [
            'type_1'=> isset($recordInfo['type_1']) ? ($recordInfo['type_1'] + count($qType['type_1'])) : count($qType['type_1']),
            'type_2'=> isset($recordInfo['type_2']) ? ($recordInfo['type_2'] + count($qType['type_2'])) : count($qType['type_2']),
            'type_3'=> isset($recordInfo['type_3']) ? ($recordInfo['type_3'] + count($qType['type_3'])) : count($qType['type_3']),
            'type_4'=> isset($recordInfo['type_4']) ? ($recordInfo['type_4'] + count($qType['type_4'])) : count($qType['type_4']),
        ];
        foreach ($qTag as $tagk=>$tagv){
            $saveRedis[$tagk] = (isset($recordInfo[$tagk]) ?  $recordInfo[$tagk] + count($tagv) : count($tagv));
        }
        RedisService::setHash('wrong_record_uid_'.$uid,$saveRedis, '', 86400, false);
        return true;
    }

    /**
     * 错误题目数量统计
     * @param $qid
     * @param $uid
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function setRecordDb($qid, $uid)
    {
        $errorQData = XmReportErrorQuestion::find()->where(['in','q_id',$qid])->andWhere(['user_id'=>$uid])->asArray()->all();

        $DBQid = $errorQData ? array_column($errorQData,'q_id') : [];
        $insertQid = array_diff($qid,$DBQid);
        $updateQid = array_intersect($qid,$DBQid);
        $time = time();
        if($insertQid){
            $insertData = [];
            foreach ($insertQid as $k=>$v ){
                $insertData[] = [
                    'user_id' => $uid,
                    'q_id'    => $v,
                    'error_count' => 1,
                    'error_type'  => 0,
                    'add_time' => $time,
                    'update_time'=>$time
                ];
            }
            Yii::$app->db->createCommand()->batchInsert(XmReportErrorQuestion::tableName(),['user_id','q_id','error_count','error_type','add_time','update_time'],$insertData)->execute();
        }
        if($updateQid){

            Yii::$app->db->createCommand()->update(XmReportErrorQuestion::tableName(),
                ['error_count'=> new \yii\db\Expression('error_count + 1'),'update_time'=>$time],['in','q_id',$updateQid])
                ->execute();
        }
        return true;
    }

    /**
     * 提交备注接口
     * @param $post
     * @return bool
     */
    public static function setErrorType($post)
    {
        $uid = UserService::$userInfo['uid'];
        $recordInfo = XmReportErrorQuestion::findOne(['user_id'=>$uid,'q_id'=>$post['q_id']]);
        if($recordInfo){
            $recordInfo -> error_type = isset($post['error_type']) ? $post['error_type'] : '';
            $recordInfo -> update_time = time();
            $recordInfo -> save();
            return true;
        }else{
            return false;
        }

    }

    /**
     * 提交备注接口
     * @param $post
     * @return bool
     */
    public static function setRemark($post)
    {
        $uid = UserService::$userInfo['uid'];
        $recordInfo = XmReportErrorQuestion::findOne(['user_id'=>$uid,'q_id'=>$post['q_id']]);
        if($recordInfo){
            $recordInfo -> remark = isset($post['remark']) ? $post['remark'] : '';
            $recordInfo -> update_time = time();
            $recordInfo -> save();
            return true;
        }else{
            return false;
        }

    }
}