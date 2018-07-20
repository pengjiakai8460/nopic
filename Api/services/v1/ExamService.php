<?php
namespace Api\services\v1;

use common\base\BaseService;
use common\models\orm\XmCExam;
use common\models\orm\XmCExamTestlets;
use common\models\orm\XmCQuestion;
use common\models\orm\XmCQuestionTags;
use common\models\orm\XmCTag;
use common\models\orm\XmCTestlets;
use common\models\orm\XmCTestletsQuestion;
use common\models\orm\XmReportErrorQuestion;
use common\models\orm\XmReportExam;
use common\models\orm\XmReportExamQuestion;
use common\models\orm\XmUserRate;
use function PHPSTORM_META\type;
use Yii;
use yii\db\Query;

class ExamService extends BaseService
{
    private static $_models = array();

    public static $q_types = [
        4 => 1,
        5 => 2,
        6 => 3,
        7 => 4,
    ];

    public static $tag_splite = 100;

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author zhangzhicheng
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            return $model;
        }
    }

    //列表
    public static function examList($user_id, $type=1,$id = 0, $limit=10)
    {
        $list = array();//列表数据
        $examList = XmCExam::find()->where(['status'=>1, 'type'=>$type])->select(' complexity,id,title,year');
        $query = clone $examList;
        $examListCount = $query->count();
        if($type == 1){
            $examList = $examList->orderBy('id desc');
        }else{
            $examList = $examList->orderBy('id desc');
        }
        if($id != 0){
            $examList = $examList->andWhere(['<', 'id', $id]);
        }
        $examList = $examList->limit($limit)->asArray()->all();
        if(!empty($examList)){
            $sub = 0;
            foreach ($examList as $key=>$value){
                $list[$sub]['year'] = $value['year'];
                $list[$sub]['star'] = $value['complexity'];

                $list[$sub]['status'] = self::userNotYetLateReportExamRecoad($user_id, $value['id']);

                $list[$sub]['isYetExam'] = self::userYetExamRecoad($user_id, $value['id']);
                //暂定的题型平均分数
                $list[$sub]['average'][] = ['title'=>'单项选择','num'=>10];
                $list[$sub]['average'][] = ['title'=>'问题求解','num'=>20];
                $list[$sub]['average'][] = ['title'=>'阅读程序','num'=>20];
                $list[$sub]['average'][] = ['title'=>'完善程序','num'=>15];

                $list[$sub]['title'] = $value['title'];
                $list[$sub]['id'] = $value['id'];
                $sub++;
            }
        }
        $ret['list'] = $list;
        $ret['count'] = $examListCount;
        return $ret;
    }

    //检查用户最近一条指定试卷的做题记录是否已经完成
    private static function userNotYetLateReportExamRecoad($user_id, $exam_id)
    {
        $ret = XmReportExam::find()->where(['user_id'=>$user_id, 'exam_id'=>$exam_id, 'status'=>1])->orderBy('id desc')->limit(1)->asArray()->all();
        if(empty($ret) || $ret[0]['is_accept'] == 1){
            return 0;
        }else{
            return 1;
        }
    }

    //检查用户是否存在未完成的做题记录(废弃)
//    private static function userNotYetExamRecoad($user_id, $exam_id)
//    {
//        $ret = XmReportExam::find()->where(['user_id'=>$user_id, 'exam_id'=>$exam_id, 'status'=>1, 'is_accept' => 0])->asArray()->all();
//        if(!empty($ret)){
//            return 1;
//        }
//        return 0;
//    }

    //检查用户是否存在指定试卷已完成的做题记录
    private static function userYetExamRecoad($user_id, $exam_id)
    {
        $ret = XmReportExam::find()->where(['user_id'=>$user_id, 'exam_id'=>$exam_id, 'status'=>1, 'is_accept' => 1])->asArray()->all();
        if(!empty($ret)){
            return 1;
        }
        return 0;
    }

    //根据套卷的做题记录查找完整的试卷信息（这里会标记出已经做的题目的原始回答）
    public static function reportExamCompleteInformation($report_id)
    {
        $reportExam = XmReportExam::find()->where(['id'=>$report_id])->asArray()->one();
        if(empty($reportExam)){
            return null;
        }
        $examCompletInformation = self::examCompleteInformation($reportExam['exam_id'], 'id,title,all_times', 'id,title,subtitle', 'id,title,answer_count,content,type', $report_id);
        $examCompletInformation['consume_times'] = $reportExam['times'];
        return $examCompletInformation;
    }

    /**试卷的全部信息(包含题组和题目的)
     * @param $exam_id
     * @param string $examCol *
     * @param string $testletCol *
     * @param string $questionCol *
     * @param int $report_id 0
     * @return array
     */
    public static function examCompleteInformation($exam_id, $examCol='*', $testletCol="*", $questionCol= "*", $report_id=0)
    {
        $ret = array();
        $exam = self::examEssentialInformation($exam_id, $examCol);
        if(empty($exam)){
           return $ret;
        }
        $ret['exam'] = $exam;
        $examTestlet = XmCExamTestlets::find()->where(['e_id'=>$exam_id, 'status'=>1])->select('t_id')->orderBy('sort ASC')->asArray()->all();
        if(empty($examTestlet)){
            $ret['testlets'] = array();
            return $ret;
        }
        $t_id_arr = array_column($examTestlet, 't_id');
        foreach($t_id_arr as $t_id){
            $ret['testlets'][] = self::testletCompleteInformation($t_id, $testletCol, $questionCol, $report_id);
        }
        return $ret;
    }


    /**试卷基本(只针对exam表中的信息)
     * @param $exam_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function examEssentialInformation($exam_id, $col = "*")
    {
        return XmCExam::find()->where(['id'=>$exam_id, 'status'=>1])->select($col)->asArray()->one();
    }


    /**题组完整的信息(包含题组内的题目的信息)
     * @param $testlet_id
     * @param string $testletCol
     * @param string $questionCol
     * @param int $report_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function testletCompleteInformation($testlet_id, $testletCol = '*', $questionCol="*", $report_id = 0)
    {
        $ret = array();
        $testlet = self::testletEssentialInformation($testlet_id, $testletCol);
        if(empty($testlet)){
            return $ret;
        }
        $testlet_question = XmCTestletsQuestion::find()->where(['t_id' => $testlet_id, 'status'=>1])->orderBy('sort ASC')->select('q_id')
//            ->createCommand()->sql;
            ->asArray()->all();
        $q_id_arr = array_column($testlet_question, 'q_id');
        $questions = self::questions($q_id_arr, $questionCol, $report_id);
        $ret = $testlet;
        $ret['questions'] = $questions;
        return $ret;
    }

    /**题组基本信息(只针对testlet表中的信息)
     * @param $testlet_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function testletEssentialInformation($testlet_id, $col)
    {
        return XmCTestlets::find()->where(['id'=>$testlet_id, 'status'=>1])->select($col)->asArray()->one();
    }

    /**题目的信息(可查询一条或者多条记录)
     * @param $q_id numeric/array  单独的id或者问题id组成的数组
     * @return array|null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function questions($q_id, $col="*", $report_id = 0)
    {
        $ret = array();
        if(strpos($col,'content')===false && $col != "*"){
            $col .= ',content';
        }
        if(strpos($col, 'title') === false && $col != "*"){
            $col .= ',title';
        }
        if(strpos($col, 'type') === false && $col != "*"){
            $col .= ',type';
        }
        $questions = XmCQuestion::find()->where(['status'=>1])->select($col);
//        return $questions;
        if(is_array($q_id)){
            $questions = $questions->andWhere(['in', 'id', $q_id])->asArray()->all();
            //对转义字符进行还原原始字符
                if (!empty($questions)) {
                    foreach ($questions as $key => $value) {
                        if($report_id != 0){
                            $reportExamQuestion = XmReportExamQuestion::find()->where(['report_id'=>$report_id, 'q_id'=>$value['id']])->asArray()->one();
                            $questions[$key]['old_value'] = null;
                            if(!empty($reportExamQuestion)){
                                if(!empty($reportExamQuestion['answer'])){
                                    $questions[$key]['old_value'] = json_decode($reportExamQuestion['answer']);
                                }
                            }
                        }
                        $questions[$key]['title'] = stripcslashes($value['title']);
                        $arr = json_decode($value['content'], true);
                        if($value['type'] == 1){
                            foreach($arr as $k=>$v){
                                foreach($v as $i => $j){
                                    if($i == "is_r" || $i=='s'){
                                        unset($arr[$k][$i]);
                                    }else{
                                        $arr[$k][$i] = html_entity_decode($j);
                                    }
                                }
                            }
                            $questions[$key]['content'] = $arr;
                        }else{
                            unset($questions[$key]['content']);
                        }
                    }
                    $ret = $questions;
                }

        }elseif(is_numeric($q_id)){
            $questions = $questions->andWhere(['id'=>$q_id])->asArray()->one();
            $questions['title'] = $questions['title'] ? stripcslashes($questions['title']) : '';
            $questions['content'] = json_decode($questions['content'], true);
        }
        return $ret;
    }

    //生成用户做套题的基本记录
    public static function addReportExam($user_id, $exam_id)
    {
        $reporptExam = new XmReportExam;
        $reporptExam->user_id = $user_id;
        $reporptExam->exam_id = $exam_id;
        $reporptExam->score = 0;
        $reporptExam->correct_rate = 0;
        $reporptExam->times = 0;
        $reporptExam->complete_state = 0;
        $reporptExam->add_time = time();
        $reporptExam->update_time = time();
        $reporptExam->status = 1;
        $reporptExam->save();
        return $reporptExam->id;
    }

    //变更用户做题的基本信息
    private static function updateReportExam($r_e_id, $data)
    {
//        $reportExam = XmReportExam::find()->where(['id'=>$r_e_id])->one();
//
//        foreach($data as $key=>$value){
//            $reportExam->$key = $value;
//        }
//        return $reportExam->save();
        return XmReportExam::updateAll($data, ['id'=>$r_e_id]);
    }

    //获取做题记录基本信息
    public static function reportExam($report_id)
    {
        return XmReportExam::find()->where(['id'=>$report_id, 'status'=>1])->select('exam_id')->asArray()->all();
    }

    /**生成用户做题的基本记录(这里要分三种情况)
     * @param $action_status 0 1，已做的放弃，开始全新的记录；2，恢复上一次做题记录 ;0,则认定为未做过的题目开新的记录
     * @param $exam_id
     * @return bool
     */
    public static function createReportExam($user_id, $exam_id, $action_status=0 )
    {
        $reportExamId = XmReportExam::find()->where(['exam_id'=>$exam_id, 'user_id'=>$user_id, 'status'=>1, 'is_accept'=>0])->max('id');
//            ->orderBy('id DESC')->select('id')->asArray()->one();
        if($action_status == 2){
            if(!empty($reportExamId)){
                return $reportExamId;
            }
            return $reportExamId;
        }elseif($action_status == 1 || $action_status ==0){
//            if(!empty($reportExam)){
//                self::updateReportExam($reportExam['id'], ['status'=>0]);
//                //这里将对应的单个做题记录全部标记为删除
//                XmReportExamQuestion::updateAll(['status'=>0], ['report_id'=>$reportExam['id']]);
//            }
            $report_id =  self::addReportExam($user_id, $exam_id);
            return $report_id;
        }
        return 0;
    }

    /**写入单题的做题记录
     * @param $user_id
     * @param $exam_id
     * @param $t_id
     * @param $q_id
     * @param $answer
     * @return bool
     */
    public static function addReportExamQuestion($report_id, $user_id, $exam_id, $t_id, $q_id, $answer)
    {
        $reportExamQuestion = new XmReportExamQuestion;
        $reportExamQuestion->report_id = $report_id;
        $reportExamQuestion->user_id = $user_id;
        $reportExamQuestion->exam_id = $exam_id;
        $reportExamQuestion->t_id = $t_id;
        $reportExamQuestion->q_id = $q_id;
        $rightAnswer = self::questionIsRight($q_id, $answer);
        $reportExamQuestion->is_right = $rightAnswer['is_right'];
        $reportExamQuestion->score = $rightAnswer['score'];
        $reportExamQuestion->right_num = $rightAnswer['right_num'];
        $reportExamQuestion->answer_note = json_encode($rightAnswer['answer_note'], true);
        $reportExamQuestion->answer = json_encode($answer,true);
        $reportExamQuestion->status = 1;
        $reportExamQuestion->is_accept = 0;
        $reportExamQuestion->add_time = time();
        $reportExamQuestion->update_time = time();
        $ret = $reportExamQuestion->save();
        return $ret;
    }

    /**判断回答是否正确的方法(返回必要的用于保存的信息)
     * @param $q_id
     * @param $answer array
     * @return array
     */
    private static function questionIsRight($q_id, $answer)
    {
        $question = XmCQuestion::find()->where(['id'=>$q_id])->asArray()->one();
        $q_type = $question['type'];
        $ret = array();//最终返回的数组，这里包含
        $content = json_decode($question['content'], true);

        if($q_type == 1) {
            $rightAnswer = array();
            foreach ($content as $key => $value) {
                if ($value['is_r'] == 1) {
                    $rightAnswer = $content[$key];
                }
            }
            if ($rightAnswer['n'] == $answer[0]) {
                $ret['is_right'] = 1;
                $ret['score'] = $rightAnswer['s'];
                $ret['right_num'] = 1;
                $ret['answer_note'] = [1];
            } else {
                $ret['is_right'] = 0;
                $ret['score'] = 0;
                $ret['right_num'] = 0;
                $ret['answer_note'] = [0];
            }
        }else{
            $score = 0;
            $right_num = 0;
            $answer_note = array();
            foreach($content as $key=>$value){
//                if($value['is_r'] == 1){
                    $rightAnswer = explode("//", $value['c']);//这一小空的正确答案的数组
                    if(in_array($answer[$key], $rightAnswer)){
                        $right_num++;
                        $score += $value['s'];
                        array_push($answer_note, 1);
                    }else{
                        array_push($answer_note, 0);
                    }
//                }
            }
            $ret['answer_note'] = $answer_note;
            $ret['score'] = $score;
            $ret['right_num'] = $right_num;
            if($right_num == $question['answer_count']){
                $ret['is_right'] = 1;
            }else{
                $ret['is_right'] = 0;
            }
        }
//        }else{
//            $score = 0;
//            $right_num = 0;
//            foreach ($content as $key=>$value){
//                if($answer[$key] == $value['c']){
//                    $right_num ++;
//                    $score += $value['s'];
//                }
//            }
//            if($right_num == $question['answer_count']){
//                $ret['is_right'] = 1;
//            }else{
//                $ret['is_right'] = 0;
//            }
//            $ret['score'] = $score;
//            $ret['right_num'] = $right_num;
//        }
        return $ret;
    }

    /**变更单题答题记录的方法
     * @param $reportExamQuestionId
     * @param $data
     * @return bool
     */
    public static function updateReportExamQuestion($reportExamQuestionId, $data)
    {
//        $ret = XmReportExamQuestion::find()->where(['id'=>$reportExamQuestionId])->one();
//        foreach ($data as $key=>$value) {
//            $ret->$key = $value;
//        }
//        $ret->update_time = time();
//        return $ret->save();
        return XmReportExamQuestion::updateAll($data, ['id'=>$reportExamQuestionId]);
    }


    /**变更题目答案的方法
     * @param $r_e_q_id
     * @param $q_id
     * @param $answer
     * @return bool
     */
    public static function updateReportQuestionAnswer($r_e_q_id, $q_id, $answer)
    {
        $questionAnswer = self::questionIsRight($q_id, $answer);
        $data['is_right'] = $questionAnswer['is_right'];
        $data['score'] = $questionAnswer['score'];
        $data['right_num'] = $questionAnswer['right_num'];
        $data['answer'] = json_encode($answer);
        $data['answer_note'] = json_encode($questionAnswer['answer_note']);
        $data['update_time'] = time();
        return self::updateReportExamQuestion($r_e_q_id, $data);
    }


    /**保存单题的前台方法
     * @param $user_id
     * @param $exam_id
     * @param $t_id
     * @param $q_id
     * @param $answer
     * @return bool
     */
    public static function saveReportExamQuestion($report_id, $user_id, $exam_id, $t_id, $q_id, $answer)
    {
        //需要判断是否已经保存对题目的答案了(如果已保存，则是修改行为)
        $reportExamQuestion = XmReportExamQuestion::find()->where(['report_id'=>$report_id,  'user_id'=>$user_id,'exam_id'=>$exam_id, 't_id'=>$t_id, 'q_id'=>$q_id, 'status' => 1])->asArray()->one();
        if(empty($reportExamQuestion)){
            return self::addReportExamQuestion($report_id, $user_id, $exam_id, $t_id, $q_id, $answer);
        }else{
            return self::updateReportQuestionAnswer($reportExamQuestion['id'], $q_id, $answer);
        }
    }

    //检查已经做题记录是否已经提交，已经提交则不做任何数据增改
    public static function isAllowChangeReport($report_id)
    {
        $reportExam = XmReportExam::find()->where(['id'=>$report_id])->select('id, is_accept')->asArray()->one();
        if($reportExam['is_accept'] == 1){
            return false;
        }
        return true;
    }

    //提交保存套卷
    public static function saveReportExam($user_id, $report_id, $times)
    {
        //检查套卷是否已经被提交
        $reportExam = XmReportExam::find()->where(['id'=>$report_id, 'status'=>1])->select('is_accept')->one();
        if(!empty($reportExam) && $reportExam['is_accept'] == 1){
            return false;
        }
        //增加没有做的题目到做题记录表中并标记为错误
        $examCount = self::addNoReportQuestion($report_id);//这里会返回试卷的题目总数
        //对所有做题记录进行状态变更
        XmReportExamQuestion::updateAll(['is_accept'=>1, 'update_time'=>time()],['report_id'=>$report_id, 'status'=>1, 'is_accept'=>0]);
        //变更做试卷记录的各种值
        $score = self::reportExamScore($report_id);//获取做题记录的总分数
        //计算正确率
        $rightQuestionCount = XmReportExamQuestion::find()->where(['report_id'=>$report_id, 'is_right'=>1, 'status'=>1])->asArray()->count('id');
        $correctRate = $rightQuestionCount == 0 ? 0 : sprintf('%2d',($rightQuestionCount / $examCount * 100));
        $data = [
            'update_time'=>time(),
            'score'=>$score,
            'is_accept'=>1,
            'times' => $times,
            'correct_rate' => $correctRate
        ];
        self::updateReportExam($report_id, $data);
        //完成上述操作后将整张卷子的做题记录汇总到user_rate表中
        self::addUserRate($report_id, $user_id);
        //添加错题记录
        WrongRecordService::setWrongRecord($report_id);
        return true;
    }

    //提交之前将未作答的题目生成对应的做题记录并标记未错误
    private static function addNoReportQuestion($report_id)
    {
        //步骤一、增加没有做的题目到做题记录表中并标记为错误
            //首先查出所有试卷中的所有题目
        $reportExam = XmReportExam::find()->where(['id'=>$report_id, 'status'=>1])->asArray()->one();
        $exam_id = $reportExam['exam_id'];
        $testletQuestions = self::examQuestionId($exam_id);//试卷题组和题目的id对应关系
        $reportQuestions = XmReportExamQuestion::find()->where(['report_id'=>$report_id, 'status'=>1])->select('q_id')->asArray()->all();//已完成的题目的数组
        if(count($testletQuestions) == count($reportQuestions)){
            return count($testletQuestions);
        }
        $exam_q_id_arr = array();//试卷全部q_id
        foreach($testletQuestions as $key => $value){
            $exam_q_id_arr[] = $value['q_id'];
        }
            //获取已经回答的题目id
        $no_report_q_id_arr =array_diff($exam_q_id_arr, array_column($reportQuestions, 'q_id'));
        $exam_q_t_arr = array();//重组数组
        foreach ($testletQuestions as $key=>$value) {
            $exam_q_t_arr[$value['q_id']] = $value['t_id'];
        }
        $data = array();//需要添加的数组
        foreach($no_report_q_id_arr as $key=>$value){
            $d['user_id'] = $reportExam['user_id'];
            $d['report_id'] = $report_id;
            $d['exam_id'] = $reportExam['exam_id'];
            $d['t_id'] = $exam_q_t_arr[$value];
            $d['q_id'] = $value;
            $d['is_right'] = 0;
            $d['answer'] = "";
            $d['times'] = 0;
            $d['right_num'] = 0;
            $d['score'] = 0;
            $d['is_accept'] = 1;
            $d['status'] = 1;
            $d['add_time'] = time();
            $d['update_time'] = time();
            $data[] = $d;
        }
        if (!empty($data)) {
            Yii::$app->db->createCommand()->batchInsert(XmReportExamQuestion::tableName(),
                ['user_id', 'report_id', 'exam_id', 't_id', 'q_id', 'is_right', 'answer', 'times', 'right_num', 'score', 'is_accept', 'status', 'add_time', 'update_time'],
                $data)->execute();
        }
        return count($testletQuestions);
    }

    //获取试卷中所有题目的t_id与q_id对应关系数组类型如
    private static function examQuestionId($exam_id)
    {
        //查询题组标题和id
        $examTestlet = XmCExamTestlets::find()->where(['e_id'=>$exam_id, 'status'=>1])->select('t_id')->asArray()->all();
        $t_id_arr = array_column($examTestlet, 't_id');
        //查询具体的题目id
        $testletQuestion = XmCTestletsQuestion::find()->where(['in', 't_id', $t_id_arr])->andWhere(['status'=>1])->select('t_id, q_id')->asArray()->all();
        return $testletQuestion;
    }

    //计算整张套卷的分数
    private static function reportExamScore($report_id)
    {
        return XmReportExamQuestion::find()->where(['report_id'=>$report_id, 'status'=>1])->sum('score');
    }

    //练习记录列表(包含分页)
    public static function practiceRecord($user_id, $type=0, $is_accept = 2, $start_time=0, $end_time=0,$page = 1, $limit=10)
    {
        $sql = "SELECT re.*,e.type,e.title FROM ".XmReportExam::tableName()." AS re LEFT JOIN ".XmCExam::tableName()." AS e ON re.exam_id = e.id WHERE re.user_id = ".$user_id." AND re.status = 1 ";
        if($type != 0){
            if($type == 3){
                $sql .= " AND e.type > 3";
            }else{
                $sql .= " AND e.type = ".$type;
            }
        }
        if($is_accept != 2){
            $sql .= " AND re.is_accept = ".$is_accept;
        }
        if($start_time && $end_time) {
            $sql .= " AND re.update_time between " . $start_time . " AND " .$end_time;
        }
        $reportExamCount = count(Yii::$app->db->createCommand($sql)->queryAll());
        //插入分页参数
        $sql .= " ORDER BY re.update_time desc LIMIT ".($limit*($page-1)).",10";
        $reportExam = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($reportExam)){
            foreach ($reportExam as $key => $value) {
                if($value['type'] > 3 ){
                    $reportExam[$key]['special_parameter'] = self::getTpIdByType($value['type']);
                }else{
                    $reportExam[$key]['special_parameter'] = ['q_type'=>0, 'type'=>0];
                }
            }
        }
        return ['list'=>$reportExam, 'count'=>$reportExamCount];
    }

    //已提交做题记录的报告
    public static function practicePresentation($report_id)
    {
        //1，检查是否 已经提交
        $reportExam = XmReportExam::find()->where(['id'=>$report_id])->asArray()->one();
        if(!(!empty($reportExam) && $reportExam['is_accept'] == 1)){
            return 0;
        }
        //试卷名称
        $exam = XmCExam::find()->where(['id'=>$reportExam['exam_id']])->select('title,all_score,all_times')->asArray()->one();
        $user_id = $reportExam['user_id'];
        //2, 获取做题记录及答案值
        $reportExamQuestion = XmReportExamQuestion::find()
            ->where([
                'report_id'=>$report_id,
                'status' => 1,
                'is_accept' => 1
            ])
            ->select('id,is_right, q_id, answer, answer_note')
            ->asArray()
            ->all();
        $q_id_arr = array_column($reportExamQuestion, 'q_id');//已经完成的题目id
        $reportQuestionAnswer = array();
        foreach($reportExamQuestion as $key=>$value){
            $reportQuestionAnswer[$value['q_id']] = $value['answer'];
        }
        $questions = XmCQuestion::find()->where(['in', 'id', $q_id_arr])->select('id,type, title,content,complexity,answer_count,explain')->asArray()->all();
        $questionTagsArr = XmCQuestionTags::find()->where(['in', 'q_id', $q_id_arr])->select('tag_id, q_id')->asArray()->all();
        $tagsQuery = XmCTag::find()->where(['in', 'id', array_column($questionTagsArr, 'tag_id')])->select('name,id')->asArray()->all();
        $tagsArr = array();
        foreach($tagsQuery as $key=>$value){
            $tagsArr[$value['id']] = $value['name'];
        }
        $questionTags = array();//重组后的题目标签对应关系数组并获取标签名
        foreach($questionTagsArr as $key => $value){
            $questionTags[$value['q_id']][] = $tagsArr[$value['tag_id']];
        }
        $typeArray = array();//包含的类型id数组
        $questions2 = array(); //重组题目详情数组
        foreach($questions as $key=>$value){
            if(!in_array($value['type'], $typeArray)){
                array_push($typeArray, $value['type']);
            }
            $questions2[$value['id']] = $value;
        }
        //类型赋值
        foreach($typeArray as $value){
            switch ($value){
                case 1:
                    $type_value[$value] = '单项选择';
                    break;
                case 2:
                    $type_value[$value] = '问题求解';
                    break;
                case 3:
                    $type_value[$value] = '阅读程序写结果';
                    break;
                case 4:
                    $type_value[$value] = '完善程序';
                    break;
                default:
                    break;
            }
        }
        //获取题目的错误记录信息
        $errorQuestion = XmReportErrorQuestion::find()->where(['in', 'q_id', $q_id_arr])->andWhere(['user_id'=>$user_id])->asArray()->all();
        //重组错误记录
//        $errorQuestion2 = array();//重组后的错误记录（以q_id为建名的数组）
        $errorQidRemark = array_column($errorQuestion, 'remark','q_id');
        $errorQidType = array_column($errorQuestion, 'error_type', 'q_id');
        //组合
        foreach($reportExamQuestion as $key=>$value){
            $reportExamQuestion[$key]['tags'] = $questionTags[$value['q_id']] ?? '';
            $reportExamQuestion[$key]['type'] = $questions2[$value['q_id']]['type'];
            $reportExamQuestion[$key]['title'] = htmlspecialchars_decode(stripcslashes($questions2[$value['q_id']]['title']));

            //增加错误类型和错误备注
            $reportExamQuestion[$key]['error_type'] = $errorQidType[$value['q_id']] ?? 0;
            $reportExamQuestion[$key]['remark'] = $errorQidRemark[$value['q_id']] ?? '';
            //处理一下题目选项
            $rightKey = array();
            $content = json_decode($questions2[$value['q_id']]['content'], true);
            foreach($content as $k=>$v){
                $content[$k]['c'] = html_entity_decode($v['c']);
                if($v['is_r'] == 1){
                    if($questions2[$value['q_id']]['type'] == 1){
                        array_push($rightKey, $v['n']);
                    }else{
                        array_push($rightKey, str_replace("//", " 或 ", $content[$k]['c']));
                    }
                }
            }
            $reportExamQuestion[$key]['right_key'] = $rightKey;
            $reportExamQuestion[$key]['content'] = $content;
            $reportExamQuestion[$key]['complexity'] = $questions2[$value['q_id']]['complexity'];
            $reportExamQuestion[$key]['answer_count'] = $questions2[$value['q_id']]['answer_count'];
            $reportExamQuestion[$key]['explain'] = htmlspecialchars_decode(stripcslashes($questions2[$value['q_id']]['explain']));
        }

        //上一个相同试卷的做题记录
        $lateReportExam = XmReportExam::find()->where(['user_id'=>$user_id, 'is_accept'=>1, 'status'=>1,'exam_id'=>$reportExam['exam_id']])->andWhere(['<', 'update_time', $reportExam['update_time']])->orderBy('update_time desc')->limit(1)->asArray()->one();

        //用户增长图的数据
        $increaseData = array();
        $increaseData['score'] = $reportExam['score'];//本次得分
        $increaseData['correct_rate'] = $reportExam['correct_rate'];
        $increaseData['times'] = $reportExam['times'];

        if(empty($lateReportExam)){
            $increaseData['score_increase'] = $reportExam['score'] - 0;
            $increaseData['correct_rate_increase'] = $reportExam['correct_rate'] - 0;
            $increaseData['times_increase'] = $reportExam['times'] - 0;
        }else{
            $increaseData['score_increase'] = $reportExam['score'] - $lateReportExam['score'];
            $increaseData['correct_rate_increase'] = $reportExam['correct_rate'] - $lateReportExam['correct_rate'];
            $increaseData['times_increase'] = $reportExam['times'] - $lateReportExam['times'];
        }
        return [
//            'lateReportExam'=>$lateReportExam,
            'reportExamQuestion'=>$reportExamQuestion,
            'nowReportExam'=>$reportExam,
            'increaseData'=>$increaseData,  'questionType'=>$type_value,
            'exam'=>$exam];
    }

    public static function addUserRate($report_id, $user_id)
    {
        $sql = "select req.is_right,cq.type from xm_report_exam_question as req left join xm_c_question as cq on req.q_id = cq.id where req.report_id = ".$report_id." and req.status = 1";
        $rate = \Yii::$app->db->createCommand($sql)->queryAll();
        $type1_right = 0; //单选题正确数目
        $type1_all = 0; //单选题总数
        $type2_right = 0; //问题求解正确数目
        $type2_all = 0; //问题求解总数目
        $type3_right = 0; //阅读程序正确数目
        $type3_all = 0; //阅读程序总数目
        $type4_right = 0; //完善程序正确数目
        $type4_all = 0; //完善程序总数目
        foreach($rate as $key=>$value){
            switch ($value['type']){
                case 1:
                    $type1_all ++;if($value['is_right']==1){$type1_right ++;}
                    break;
                case 2:
                    $type2_all ++;if($value['is_right']==1){$type2_right ++;}
                    break;
                case 3:
                    $type3_all ++;if($value['is_right']==1){$type3_right ++;}
                    break;
                case 4:
                    $type4_all ++;if($value['is_right']==1){$type4_right ++;}
                    break;
            }
        }
        $userRate = XmUserRate::find()->where(['uid'=>$user_id])->asArray()->one();
        if(empty($userRate)){
            $addData = [
                'uid' => $user_id,
                'type1_right' => $type1_right,
                'type1_all' =>$type1_all,
                'type2_right' => $type2_right,
                'type2_all' =>$type2_all,
                'type3_right' => $type3_right,
                'type3_all' =>$type3_all,
                'type4_right' => $type4_right,
                'type4_all' =>$type4_all,
                'add_time' => time(),
                'update_time' => time(),
                'status' => 1
            ];
            \Yii::$app->db->createCommand()->insert(XmUserRate::tableName(), $addData)->execute();
        }else{
            $updateData = [
                'type1_right' => $type1_right + $userRate['type1_right'],
                'type1_all' => $type1_all + $userRate['type1_all'],
                'type2_right' => $type2_right + $userRate['type2_right'],
                'type2_all' => $type2_all + $userRate['type2_all'],
                'type3_right' => $type3_right + $userRate['type3_right'],
                'type3_all' => $type3_all + $userRate['type3_all'],
                'type4_right' => $type4_right + $userRate['type4_right'],
                'type4_all' => $type4_all + $userRate['type4_all'],
                'update_time' => time()
            ];
            XmUserRate::updateAll($updateData,['uid'=>$user_id]);
        }
    }

    //验证操作记录的人与当前登录人员是否为同一人
    public static function userIsIdentical($report_id)
    {
        $reportExam = XmReportExam::find()->where(['id'=>$report_id])->select('user_id')->asArray()->one();
        $userInfo = UserService::$userInfo;

        if($reportExam['user_id'] == $userInfo['uid']){
            return true;
        }
        return false;
    }

    //添加做题记录的笔记
    public static function addRemake($r_q_id, $error_type = null,$remark = null)
    {
        $data = array();
        if(!empty($error_type)){
            $data['error_type'] = $error_type;
        }
        if(!empty($remark)){
            $data['remark'] = $remark;
        }
        if(!empty($data)){
            return self::updateReportExamQuestion($r_q_id, $data);
        }
        return 0;
    }

    //保存做题时间
    public static function saveConsumeTimes($report_id, $times)
    {
        $reportExam = XmReportExam::find()->where(['id'=>$report_id])->asArray()->one();
        if($reportExam['is_accept'] == 1){
            return 'fail';
        }
        return self::updateReportExam($report_id, ['times'=>$times]);
    }


    //根据试卷type 求qtype和对应type
    public static function getTpIdByType($type) {
        $arr = [];
        if (isset(self::$q_types[$type])) {
            $arr['q_type'] = 1;
            $arr['type'] = self::$q_types[$type];
        }

        if ($type > self::$tag_splite) {
            $arr['q_type'] = 1;
            $arr['type'] = $type - self::$tag_splite;
        }
        return $arr;

    }
}