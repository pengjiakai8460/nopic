<?php
namespace Admin\service;

use common\models\orm\XmCExam;
use common\models\orm\XmCExamTestlets;
use common\models\orm\XmCQuestion;
use common\models\orm\XmCQuestionTags;
use common\models\orm\XmCTag;
use common\models\orm\XmCTestlets;
use common\models\orm\XmCTestletsQuestion;
use yii\data\Pagination;
use yii\db\Query;
use Yii;

class ExamsService extends BaseService
{

    const QUESTION_TYPE = [
        1 => [
            'name' => '单项选择题',
            'desc' => '四大题型中的第一个，大多数是四选一，每题有且仅有一个正确选项。'
        ],

        2 => [
            'name' => '问题求解',
            'desc' => '四大题型中的第二个，根据题干描述的问题分析解题思路写出对应答案。'
        ],

        3 => [
            'name' => '阅读程序写结果',
            'desc' => '四大题型中的第三个，阅读给出的程序，根据输入内容写出输出结果。'
        ],

        4 => [
            'name' => '完善程序',
            'desc' => '四大题型中的第四个，根据题目要求将题干中的程序补充完整。'
        ],
    ];

    //根据题目类型获取在试卷表对应类型
    const QUESTION_EXAM_TYPES = [
        1 => 4,   //单项选择题
        2 => 5,   //问题求解
        3 => 6,   //阅读程序写结果
        4 => 7,   //完善程序
    ];

    //其他试卷和正常试卷映射间隔值
    const TYPE_SPLITE = 100;

    const SINGLE_CHECK = 1; //单项选择
    const PROBLEM_SOLVING = 2; //问题求解
    const READING_PROBLEM = 3; //阅读程序写结果
    const PERFECT_PROBLEM = 4; //完善程序

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return UsersManageService
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**获取试卷列表
     * @param array $where 搜索条件
     * @param int $limit 获取的记录数
     * @param int $page 页码
     * @param string $order 排序依据
     * @return array
     */
    public static function getAllExamsList($where=[],$limit = 10,$page = 1,$order = 'id DESC')
    {
        $examslist = XmCExam::find()->where(['<', 'type', 3])->orWhere(['>', 'type', 3]);
        foreach($where as $key=>$value){
            switch ($key){
                case 'title':
                    $examslist->andWhere(['like', 'title', $value]); break;
                case 'id':
                    $examslist->andWhere(['id'=>$value]);break;
                case 'type':
                    $examslist->andWhere(['type'=>$value]);break;
            }
        }
        $query = clone $examslist;
        $count = $query->count();
        $examslist = $examslist->limit($limit)->offset($limit*($page-1))->orderBy($order)->asArray()->all();
        return ['count'=>$count, 'list'=>$examslist];
    }


    /** 禁用/启用试卷
     * @param $id 试卷id
     * @return int
     */
    public static function changeExamStatus($id)
    {
        $exam = XmCExam::find()->where(['id'=>$id])->asArray(true)->one();
        if($exam['status'] == 1){
            $ret = XmCExam::updateAll(['status'=>0],['id'=>$id]);
        }else{
            //启用套卷前检查试卷内是否有题目(没有题目则无法保存)
            $examTestlet = XmCExamTestlets::find()->where(['e_id'=>$id, 'status'=>1])->select('e_id, t_id')->asArray()->all();
            if(empty($examTestlet)){
                return 2;
            }else{
                $testletArr = array_column($examTestlet, 't_id');
                $testletQuestion = XmCTestletsQuestion::find()->where(['in', 't_id', $testletArr])->andWhere(['status'=>1])->orderBy('sort ASC')->asArray()->all();
                if(empty($testletQuestion)){
                    return 2;
                }
            }
            $ret = XmCExam::updateAll(['status'=>1],['id'=>$id]);
        }
        return $ret;
    }

    /** 用于创建一份全新的试卷时组装试卷(废弃)
     * @param $data
     */
//    public static function addCompleteExam($data)
//    {
//        $exam = $data['exam'];//试卷基本信息
//        $e_id = self::addExam($exam['title'], $exam['type'], $exam['year'],$exam['all_score'], $exam['all_times']);
//        //创建并添加题组到试卷中
//        if(!empty($data['testlet'])){
//            $testlet = $data['testlet'];//题组信息
//            foreach($testlet as $key => $value){
//                //添加题组信息
//                $t_id = self::addTestlet($value['title'], $value['subtitle']);
//                //添加题组试卷的关系
//                self::addExamTestlet($e_id, $t_id, $value['type']);
//                //创建题目题组的关系
//                $sort = 1;//顺序
//                foreach($value['questions'] as $k => $v){
//                    self::addTestletQuestion($t_id,$v['id'], $sort);
//                    $sort++;
//                }
//            }
//        }
//
//    }

    /**添加试卷基本信息
     * @param $title
     * @param int $type
     * @param int $all_score
     * @param int $all_times
     * @return bool
     */
    public static function addExam($title, $type=1,$complexity = 3, $year = 0, $all_score = 100,$all_times=0)
    {
        $adder_id = $_SESSION['uid'];
        $exam = new XmCExam;
        $exam->title = $title;
        $exam->type = $type;
        if($type == 1){
            $exam->year = $year;
        }
        $exam->complexity = $complexity;
        $exam->all_score = $all_score;
        $exam->task_date = 0;
        $exam->all_times = 0;
        $exam->status = 0;
        $exam->all_times = $all_times;
        $exam->adder_id = $adder_id;
        $exam->add_time = time();
        $exam->update_time = time();
        $exam->save();
        return $exam->id;
    }

    /**往试卷中添加题组
     * @param $e_id
     * @param $t_id
     * @param int $sort
     * @return bool
     */
    public static function addExamTestlet($e_id, $t_id, $sort)
    {
        $adder_id = $_SESSION['uid'];
        $examTestlet = new XmCExamTestlets();
        $examTestlet->e_id = $e_id;
        $examTestlet->t_id = $t_id;
        $examTestlet->sort = $sort;
        $examTestlet->adder_id = $adder_id;
        $examTestlet->status = 1;
        $examTestlet->add_time = time();
        $examTestlet->update_time = time();
        $examTestlet->save();
        return $examTestlet->id;
    }

    /**创建题组添加相应的记录
     * @param $title 题组标题
     * @param $subtitle 题组副标题
     * @param $type 题组类型
     * @return bool
     */
    public static function addTestlet($title, $subtitle, $type=0)
    {
       $testlet = new XmCTestlets;
       $testlet->title = $title;
       $testlet->subtitle = $subtitle;
       $testlet->type = $type;
       $testlet->add_time = time();
       $testlet->update_time = time();
       $testlet->status = 1;
       $testlet->save();
       return $testlet->id;
    }

    /** 往题组中添加题目
     * @param $t_id 题组id
     * @param $q_id 题目id
     * @param int $sort 排序序号
     * @return bool
     */
    public static function addTestletQuestion($t_id, $q_id, $sort = 1)
    {
        $adder_id = $_SESSION['uid'];
        $testletQuestion = new XmCTestletsQuestion;
        $testletQuestion->t_id = $t_id;
        $testletQuestion->q_id = $q_id;
        $testletQuestion->sort = $sort;
        $testletQuestion->status = 1;
        $testletQuestion->adder_id = $adder_id;
        $testletQuestion->update_time = time();
        $testletQuestion->add_time = time();
        $testletQuestion->save();
        return $testletQuestion->id;
    }


    public static function addQuestion($t_id, $q_id)
    {
        //按照排序查找上一个题组中的题目sort数是多少
        $maxSort = XmCTestletsQuestion::find()->where(['t_id'=>$q_id])->max('sort');
        if(empty($maxSort)){
            $maxSort = 0;
        }
        $maxSort ++;
        //添加题组和题目的关系到数据库中
        self::addTestletQuestion($t_id, $q_id, $maxSort);
    }

    /** 一个题目的基本信息
     * @param $id   题目的id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getQuestion($id)
    {
        return XmCQuestion::find()->where(['id'=>$id])->asArray()->one();
    }

    /**获取完整试卷的信息
     * @param $e_id     试卷id
     * @return array
     */
    public static function getCompleteExam($e_id){
        $ret = array();
        $exam = XmCExam::find()->where(['id'=>$e_id])->asArray()->one();
        $ret['exam'] = $exam;
        $exam_testlet = XmCExamTestlets::find()->where(['e_id'=>$e_id, 'status'=>1])->orderBy('sort ASC')->asArray()->select('t_id, sort')->all();
        $i = 0;
        foreach($exam_testlet as $key=>$value){
            $testlet = XmCTestlets::find()->where(['id'=>$value['t_id']])->asArray()->one();
            $ret['testlet'][$i] = $testlet;
            $ret['testlet'][$i]['sort'] = $value['sort'];
            $testlet_question = XmCTestletsQuestion::find()->where(['t_id'=>$value['t_id'], 'status'=>1])->orderBy('sort ASC')->select('q_id,sort')->asArray()->all();
            $j = 0;

            foreach($testlet_question as $k=>$v){
                $question = XmCQuestion::find()->where(['id'=>$v['q_id'], 'status'=>1])->asArray()->one();
                if(empty($question)){
                    continue;
                }
                $question['sort'] = $v['sort'];
                $question['title'] = !empty($question['title']) ? stripcslashes($question['title']) : '';
                $question['title'] = htmlspecialchars_decode($question['title']);
//                $questions['title'] = !empty($question['title']) ? htmlspecialchars_decode(str$question['title']) : '';
                $question['content'] = json_decode($question['content'], true);
                foreach($question['content'] as $ke => $va){
                    $xuan_content[$va['n']] = html_entity_decode($va['c']);
                }
                $question['content'] = $xuan_content;
                $ret['testlet'][$i]['questions'][$j] = $question;
                $j++;
            }
            $i++;
        }
        return $ret;
    }

    /**获取试卷的题组信息
     * @param $exam_id  试卷的id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllTestlet($exam_id)
    {
        $exam_testlet = XmCExamTestlets::find()->where(['e_id'=>$exam_id,'status'=>1])->orderBy('sort ASC')->asArray()->select('t_id, sort')->all();
        $testlets = XmCTestlets::find()->where(['in', 'id', array_column($exam_testlet, 't_id')])->select('*')->asArray()->all();
        $exam_testlet = array_combine(array_column($exam_testlet, 't_id'), $exam_testlet);
        foreach($testlets as $key=>$value){
            $testlets[$key]['sort'] = $exam_testlet[$value['id']]['sort'];
        }
        return $testlets;
    }

    /**获取试卷基本信息
     * @param $exam_id  试卷id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getExam($exam_id)
    {
        return XmCExam::find()->where(['id'=>$exam_id])->asArray()->one();
    }

    /**题组的基本信息
     * @param $testlet_id 题组id
     * @return array|null|\yii\db\ActiveRecord 返回的只是题组的基本信息
     */
    public static function getTestlet($testlet_id)
    {
        return XmCTestlets::find()->where(['id'=>$testlet_id])->asArray()->one();
    }

    //变更题组信息包括exam_testlet表中的排序字段

    /**
     * @param $exam_id  试卷的id
     * @param $t_id     题组的id
     * @param $title    题组的标题
     * @param $subtitle 题组的副标题
     * @param $sort     试卷中题组排序的序号
     */
    public static function updateTestlet($exam_id, $t_id, $title, $subtitle, $sort)
    {
        XmCTestlets::updateAll(['title' => $title, 'subtitle'  => $subtitle, 'update_time' => time()],['id'=>$t_id]);
        XmCExamTestlets::updateAll(['sort' => $sort, 'update_time' => time()], ['t_id'=>$t_id, 'e_id'=>$exam_id]);
    }

    /**题组中所有的题目
     * @param $t_id 题组id
     * @return array
     */
    public static function getTestletQuestions($t_id)
    {
        $testletQuestions = XmCTestletsQuestion::find()->where(['t_id'=>$t_id,'status'=>1])->orderBy('sort ASC')->select('id, q_id, sort')->asArray()->all();
        $ret = array();
        foreach($testletQuestions as $key=>$value){
            $questions = XmCQuestion::find()->where(['id'=>$value['q_id']])->asArray()->one();
            $questions['sort'] = $value['sort'];
            $questions['t_q_id'] = $value['id'];
            $ret[]= $questions;
        }
        return $ret;
    }

    /**删除题组题目关系
     * @param $t_q_id
     * @return int
     */
    public static function delQuestion($t_id, $q_id)
    {
        return XmCTestletsQuestion::updateAll(['status'=>0], ['t_id'=>$t_id, 'q_id'=>$q_id]);
    }



    /**题组中题目变换顺序的行为方法
     * @param $last_q_id 向下移动的题组题目关系记录的id
     * @param $t_q_id   需要排序上移的题组题目关系记录的id
     */
    public static function upTestletQuestionSort($last_q_id, $t_q_id)
    {
        $last_sort = XmCTestletsQuestion::find()->where(['id'=>$last_q_id])->asArray()->one()['sort'];
        $sort = XmCTestletsQuestion::find()->where(['id'=>$t_q_id])->asArray()->one()['sort'];
        XmCTestletsQuestion::updateAll(['sort'=>$last_sort],['id'=>$t_q_id]);
        XmCTestletsQuestion::updateAll(['sort'=>$sort],['id'=>$last_q_id]);
    }


    /**更新试卷基本信息
     * @param $exam_id 套题id
     * @param $data 变更数据的数组
     * @return int
     */
    public static function updateExam($exam_id, $data)
    {
        $data['update_time'] = time();
        return XmCExam::updateAll($data, ['id'=>$exam_id]);
    }


    /**删除题组和试卷的关系
     * @param $e_id 套题id
     * @param $t_id 题组id
     * @return bool
     */
    public static function delExamTestlet($e_id, $t_id)
    {
        //1，移除题组中所有的题目
//        $testletQuestions = self::getTestletQuestions($t_id);
        XmCTestletsQuestion::updateAll(['status'=>0], ['t_id'=>$t_id]);
        //2，移除题组
        XmCExamTestlets::updateAll(['status'=>0], ['e_id'=>$e_id, 't_id'=>$t_id]);

//        if(empty($testletQuestions)){
//            XmCExamTestlets::updateAll(['status'=>0], ['e_id'=>$e_id, 't_id'=>$t_id]);
//            return true;
//        }else{
//            return false;
//        }
    }

    //自动根据类型生成试卷
    public static function autoSave($type, $limit) {
        $time = time();
        $questions = XmCQuestion::find()->where(['type' => $type, 'status' => 1])->asArray()->all();
        $sum = count($questions);
        if ($sum == 0) {
            return false;
        }
        $nums = ceil($sum / $limit);

        for ($i = 1; $i <= $nums; $i++) {
            $title = self::QUESTION_TYPE[$type]['name'] . "第{$i}套练习";
            $exam = new XmCExam();
            $exam->title = $title;
            $exam->type = self::QUESTION_EXAM_TYPES[$type];
            $exam->year = 0;
            $exam->complexity = 4;
            $exam->all_score = 0;
            $exam->adder_id = $_SESSION['uid'];
            $exam->task_date = 0;
            $exam->all_times = 0;
            $exam->add_time = $time;
            $exam->update_time = $time;
            $exam->status = 1;
            $exam->save();
            $eid = $exam->id;
            if (!$eid) {
                return false;
            }

            $testLets = new XmCTestlets();
            $testLets->title = self::QUESTION_TYPE[$type]['name'];
            $testLets->subtitle = self::QUESTION_TYPE[$type]['name'];
            $testLets->day = 0;
            $testLets->type = 0;
            $testLets->status = 1;
            $testLets->add_time = $time;
            $testLets->update_time = $time;
            $testLets->save();
            $tid = $testLets->id;
            if (!$tid) {
                return false;
            }

            $examT = new XmCExamTestlets();
            $examT->e_id = $eid;
            $examT->t_id = $tid;
            $examT->sort = 1;
            $examT->adder_id = $_SESSION['uid'];
            $examT->status = 1;
            $examT->add_time = $time;
            $examT->update_time = $time;
            $examT->save();
            $etid = $examT->id;
            if (!$etid) {
                return false;
            }

            //先对题目
            $offset = ($i - 1) * $limit;
            $quests = array_slice($questions, $offset, $limit);
            $inData = [];
            foreach ($quests as $k => $v) {
                $inData[] = [
                    't_id' => $tid,
                    'q_id' => $v['id'],
                    'sort' => $k + 1,
                    'adder_id' => $_SESSION['uid'],
                    'status' => 1,
                    'add_time' => $time,
                    'update_time' => $time
                ];
            }
            if ($inData) {
                Yii::$app->db->createCommand()->batchInsert(XmCTestletsQuestion::tableName(),
                    ['t_id', 'q_id', 'sort', 'adder_id', 'status', 'add_time', 'update_time'],
                    $inData)->execute();
            }
        }

        return true;
    }

    //根据知识点标签自动生成试卷
    public static function autoSaveByTid($tid) {
        $time = time();
        $tag = XmCTag::find()->where(['id' => $tid, 'status' => 1])->asArray()->one();
        if (empty($tag)) {
            return false;
        }
        $qids = XmCQuestionTags::find()->select('q_id')->where(['tag_id' => $tid, 'status' => 1])->asArray()->all();
        if (!$qids) {
            return false;
        }

        $qids = array_unique(array_column($qids, 'q_id', ''));
        $quests = XmCQuestion::find()->where(['in', 'id', $qids])->andWhere(['status' => 1])->asArray()->all();
        if (!$quests) {
            return false;
        }

        $title = $tag['name'] . "的专项练习题";

        $exam = new XmCExam();
        $exam->title = $title;
        $exam->type = $tid + self::TYPE_SPLITE;
        $exam->year = 0;
        $exam->complexity = 4;
        $exam->all_score = 0;
        $exam->adder_id = $_SESSION['uid'];
        $exam->task_date = 0;
        $exam->all_times = 0;
        $exam->add_time = $time;
        $exam->update_time = $time;
        $exam->status = 1;
        $exam->save();
        $eid = $exam->id;
        if (!$eid) {
            return false;
        }

        $types = array_unique(array_column($quests, 'type', ''));
        foreach ($types as $key => $val) {
            $testLets = new XmCTestlets();
            $testLets->title = self::QUESTION_TYPE[$val]['name'];
            $testLets->subtitle = self::QUESTION_TYPE[$val]['name'];
            $testLets->day = 0;
            $testLets->type = 0;
            $testLets->status = 1;
            $testLets->add_time = $time;
            $testLets->update_time = $time;
            $testLets->save();
            $tids[$val] = $testLets->id;

            $inData[] = [
                'e_id' => $eid,
                't_id' => $tids[$val],
                'sort' => $key + 1,
                'adder_id' => $_SESSION['uid'],
                'status' => 1,
                'add_time' => $time,
                'update_time' => $time,
            ];
        }

        if ($inData) {
            Yii::$app->db->createCommand()->batchInsert(XmCExamTestlets::tableName(),
                ['e_id', 't_id', 'sort', 'adder_id', 'status', 'add_time', 'update_time'],
                $inData)->execute();
        }

        $inDatas = [];
        foreach ($quests as $k => $v) {
            $inDatas[] = [
                't_id' => $tids[$v['type']],
                'q_id' => $v['id'],
                'sort' => $k + 1,
                'adder_id' => $_SESSION['uid'],
                'status' => 1,
                'add_time' => $time,
                'update_time' => $time
            ];
        }
        if ($inDatas) {
            Yii::$app->db->createCommand()->batchInsert(XmCTestletsQuestion::tableName(),
                ['t_id', 'q_id', 'sort', 'adder_id', 'status', 'add_time', 'update_time'],
                $inDatas)->execute();
        }

        return true;





    }

}
