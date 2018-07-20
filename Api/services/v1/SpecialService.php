<?php

namespace Api\services\v1;

use common\models\orm\XmCExamTestlets;
use common\models\orm\XmCQuestion;
use common\models\orm\XmCTag;
use common\models\orm\XmCExam;
use common\models\orm\XmCTestlets;
use common\models\orm\XmCTestletsQuestion;
use common\models\orm\XmReportExam;
use common\models\orm\XmReportExamQuestion;
use Yii;
use common\base\BaseService;
use yii\web\User;

class SpecialService extends BaseService
{

    const PER_PAGE_SIZE = 10;

    //const 获取只是标签
    const BIG_TAGS = [
        1 => "计算机基本常识",
        2 => "学科知识",
        3 => "网络基本知识",
        4 => "算法基础知识",
        5 => "数据结构",
        6 => "阅读分析程序",
    ];

    const ICON_URL = [
        1 => "http://oss.xiaoma.wang/Uploads/Picture/noipc/special-navbar.789618a.png",
        2 => "http://oss.xiaoma.wang/Uploads/Picture/noipc/2.png",
        3 => "http://oss.xiaoma.wang/Uploads/Picture/noipc/special-navbar3.ce8e6d9.png",
        4 => "http://oss.xiaoma.wang/Uploads/Picture/noipc/3.png",
        5 => "http://oss.xiaoma.wang/Uploads/Picture/noipc/4.png",
        6 => "http://oss.xiaoma.wang/Uploads/Picture/noipc/5.png",
    ];

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

    const QTYPE_TYPE = 1; //题型标签1
    const QTYPE_TAGS = 2; //知识标签类型2

    const SINGLE_CHECK = 1; //单项选择
    const PROBLEM_SOLVING = 2; //问题求解
    const READING_PROBLEM = 3; //阅读程序写结果
    const PERFECT_PROBLEM = 4; //完善程序

    const TYPE_EXAM_URL = "/v1/special/index/typeexam?type=";
    const TAG_EXAM_URL = "/v1/special/index/tagexam?id=";

    private static $_models = array();

    /**
     * 初始化，每个Service都必须执行此方法
     *
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

    /**
     * 写入充值记录表
     * @param array $data
     * @return boolean
     */
    public static function getTagLists()
    {
        $user = UserService::$userInfo;
        $uid = $user['uid'] ?? 0;
        $tags = XmCTag::find()->select("id, pid, name")->where(['status' => 1])->asArray()->all();
        $tree = array();
        //第一步，将分类id作为数组key,并创建children单元
        foreach($tags as $category){
            $tree[$category['id']] = $category;
            $tree[$category['id']]['children'] = array();
        }

        //第二步，利用引用，将每个分类添加到父类children数组中，这样一次遍历即可形成树形结构。
        foreach($tree as $key=>$item){
            if($item['pid'] != 0){
                $tree[$item['pid']]['children'][] = &$tree[$key];//注意：此处必须传引用否则结果不对
                if($tree[$key]['children'] == null){
                    unset($tree[$key]['children']); //如果children为空，则删除该children元素（可选）
                }
            }
        }
        //第三步，删除无用的非根节点数据
        foreach($tree as $key => $category){
            if($category['pid'] != 0){
                unset($tree[$key]);
            }
        }

        foreach($tree as $key => $category){
            //$tree[$key]['qtype'] = self::QTYPE_TAGS;
            $k = array_search($category['name'], self::BIG_TAGS);
            if ($k) {
                $tree[$key]['img'] = self::ICON_URL[$k];
            } else {
                $tree[$key]['img'] = '';
            }
            if (isset($category['children']) && !empty($category['children'])) {
                foreach ($category['children'] as $k => $v) {
                    $tree[$key]['children'][$k]['id'] = $v['id'];
                }
            }
        }

        $three_ids = [];
        foreach ($tree as $k => $v) {
            if (isset($v['children'])) {
                foreach ($v['children'] as $m => $n) {
                    if (isset($n['children'])) {
                        foreach ($n['children'] as $i => $j) {
                            $three_ids[] = $j['id'] + self::TYPE_SPLITE;
                        }
                    }
                }
            }
        }

        $three_ids = array_unique($three_ids);
        $allExams = XmCExam::find()->where(['in', 'type', $three_ids])->andWhere(['status' => 1])->asArray()->all();

        $rep = [];
        if ($uid) {
            $ids = array_column($allExams, 'id');
            $reports = XmReportExam::find()->where(['in', 'exam_id', $ids])->andWhere(['user_id' => $uid, 'status' => 1])->orderBy("id desc")->asArray()->all();
            if ($reports) {
                foreach ($reports as $key => $v) {
                    if (!isset($rep[$v['exam_id']])) {
                        $rep[$v['exam_id']]['complete_state'] = $v['complete_state'];
                        $rep[$v['exam_id']]['id'] = $v['id'];
                    }
                }
            }
        }
        $types = [];
        foreach ($allExams as $k => $v) {
            $tp = $v['type'] - self::TYPE_SPLITE;
            $types[$tp][] = [
                'name' => $v['title'],
                'id' => $v['id'],
                'type' => self::QTYPE_TAGS,
                'rep_id' => isset($rep[$v['id']]['complete_state']) ? ($rep[$v['id']]['complete_state'] ? 0 : $rep[$v['id']]['id']) : 0,
            ];
        }

        //把试卷信息组合到知识点标签里面去
        foreach ($tree as $k => $v) {
            if (isset($v['children'])) {
                foreach ($v['children'] as $m => $n) {
                    if (isset($n['children'])) {
                        foreach ($n['children'] as $i => $j) {
                            //$three_ids[] = $j['id'] + self::TYPE_SPLITE;
                            $da = $types[$j['id']] ?? [];
                            $tree[$k]['children'][$m]['children'][$i]['exam'] = $da;
                        }
                    }
                }
            }
        }

        $trees['list'] = $tree;
        //$trees['exams'] = $types;
        return $trees;
    }

    public static function getQtypeList() {
        $user = UserService::$userInfo;
        $uid = $user['uid'] ?? 0;
        $typess = self::QUESTION_TYPE;
        foreach ($typess as $key => $val) {
            $types['types'][$key]['qtype'] = self::QTYPE_TYPE;
            $types['types'][$key]['name'] = $val['name'];
            $types['types'][$key]['desc'] = $val['desc'];
        }

        $t_types = array_values(self::QUESTION_EXAM_TYPES);
        $allExams = XmCExam::find()->where(['in', 'type', $t_types])->andWhere(['status' => 1])->asArray()->all();

        $rep = [];
        if ($uid) {
            $ids = array_column($allExams, 'id');
            $reports = XmReportExam::find()->where(['in', 'exam_id', $ids])->andWhere(['user_id' => $uid, 'status' => 1])->orderBy("id desc")->asArray()->all();
            if ($reports) {
                foreach ($reports as $key => $v) {
                    if (!isset($rep[$v['exam_id']])) {
                        $rep[$v['exam_id']]['complete_state'] = $v['complete_state'];
                        $rep[$v['exam_id']]['id'] = $v['id'];
                    }
                }
            }
        }
        foreach ($allExams as $k => $v) {
            $tp = array_search($v['type'], self::QUESTION_EXAM_TYPES);
            $types['exams'][$tp][] = [
                'name' => $v['title'],
                'id' => $v['id'],
                'rep_id' => isset($rep[$v['id']]['complete_state']) ? ($rep[$v['id']]['complete_state'] ? 0 : $rep[$v['id']]['id']) : 0,
            ];
        }

        $trees['exams'] = $types;
        return $types;
    }

    public static function getQuestionByType($where) {
        //$userInfo = Yii::$app->user;
        //$token = isset($_COOKIE['token_key']) ? $_COOKIE['token_key'] : '';
        $userInfo = UserService::$userInfo;
        $ti = time();
        //先根据试卷查询拥有几个试卷组
        $exam = XmCExam::find()->where(['id' => $where['e_id'], 'status' => 1])->asArray()->one();
        $testlets = XmCExamTestlets::find()->where(['e_id' => $where['e_id'], 'status' => 1])->asArray()->all();
        $t_ids = array_column($testlets, 't_id');
        $ret = $quest = [];
        $t_id = implode(',', $t_ids);
        if ($t_id) {
            $sql = "SELECT t.t_id, q.* FROM xm_c_testlets_question t 
                    left join xm_c_question q on q.id = t.q_id 
                    where t.t_id in ({$t_id}) and t.status = 1 and q.status = 1";
            $quests = Yii::$app->db->createCommand($sql)->queryAll();
            $ret['exam']['e_id'] = $where['e_id'];
            //如果是继续做题  需要给出结果
            if (isset($where['rep_id']) && $where['rep_id'] > 0) {
                $rep_id = $where['rep_id'];
                $report['exam'] = XmReportExam::find()->select("exam_id, times as consume_times")->where(['id' => $where['rep_id'], 'status' => 1])->asArray()->all();
                $questList = XmReportExamQuestion::find()->where(['report_id' => $where['rep_id']])->asArray()->all();
                //print_r(json_encode($questList));exit;
                if (!empty($questList)) {
                    foreach ($questList as $key => $val) {
                        $report['quest'][$val['q_id']] = $val;
                    }
                } else {
                    //return self::error(0, "该答题记录不存在!");
                }
                $ret['report'] = $report;
            } else {
                //如果是重新做题,则需要生成一条记录
                $rep = new XmReportExam();
                $rep->user_id = !empty($userInfo['uid']) ? $userInfo['uid'] : 0;
                $rep->exam_id = $where['e_id'];
                $rep->score = 0;
                $rep->correct_rate = 0;
                $rep->times = 0;
                $rep->complete_state = 0;
                $rep->add_time = $ti;
                $rep->update_time = $ti;
                $rep->status = 1;
                $rep->save();
                $rep_id = $rep->id;
                $ret['exam']['consume_times'] = 0;

            }

            foreach ($quests as $key => $val) {
                if (isset($val['content']) && !empty($val['content'])) {
                    $conts = json_decode($val['content'], true);
                    foreach ($conts as $m => $n) {
                        $conts[$m]['c'] = htmlspecialchars_decode($n['c']);
                        if ($val['type'] == self::SINGLE_CHECK) {
                            unset($conts[$m]['is_r']);
                        } else {
                            unset($conts[$m]['is_r']);
                            unset($conts[$m]['c']);
                        }
                    }
                    $val['content'] = json_encode($conts);
                    $val['title'] = htmlspecialchars_decode(stripcslashes($val['title']));
                    //$val['explain'] = htmlspecialchars_decode($val['title']);
                }
                //判断该题目是否有答案
                $val['my_answer'] = '';
                if (isset($report['quest'][$val['id']])) {
                    $val['my_answer'] = $report['quest'][$val['id']]['answer'] ?? '';
                }
                $quest[$val['type']]['lists'][] = $val;
                $quest[$val['type']]['title'] = self::QUESTION_TYPE[$val['type']]['name'];
            }
            $ret['exam']['rep_id'] = $rep_id;
            $ret['exam']['data'] = $quest;

            if ($where['qtype'] == 1) {
                $title = self::QUESTION_TYPE[$where['type']]['name'] . '的专项练习题, 共' . count($quests) . "题目";
                $ret['exam']['title'] = $title;
            } else {
                $tag_id = $exam['type'] - self::TYPE_SPLITE;
                $tag = XmCTag::find()->where(['id' => $tag_id])->asArray()->one();
                $title = $tag['name'] . '的专项练习题, 共' . count($quests) . "题目";
                $ret['exam']['title'] = $title;
            }
        }
        return $ret;
    }

    //提交试卷
    public static function submit($data) {
        //$token = isset($_COOKIE['token_key']) ? $_COOKIE['token_key'] : '';
        //$userInfo = UserService::auth($token);
        $userInfo = UserService::$userInfo;
        $ti = time();
        //判断总分数为多少
        $exam_score = XmReportExamQuestion::find()->select("sum(`score`) as score, user_id")->where(['report_id' => $data['rep_id'], 'status' => 1])->asArray()->one();
        $report = XmReportExam::findOne(['id' => $data['rep_id']]);
        if ($report) {
            $report->complete_state = 1;
            $report->is_accept = 1;
            $report->update_time = $ti;
            $report->times = $data['times'];
            $report->score = $exam_score['score'] ?? 0;
            $r = $report->save();
        } else {
            return self::error(0, "该答卷不存在!");
        }
        //$questReport = XmReportExamQuestion::findAll(['report_id' => $data['rep_id'], 'exam_id' => $data['e_id']]);

        //判断white_ids是否为空 如果不为空,则需要插入
        self::insertEmptyQuestion($data);

        $questReport = XmReportExamQuestion::updateAll(['is_accept' => 1, 'update_time' => $ti],
            "report_id = :report_id and exam_id = :exam_id", [":report_id"=> $data['rep_id'], ":exam_id" => $data['e_id']]);

        //错题集合
        WrongRecordService::setWrongRecord($data['rep_id']);

        ExamService::addUserRate($data['rep_id'], $userInfo['uid']);

        if (!$r || !$questReport) {
            return self::error(0, '保存失败,请稍后重试!');
        }
        return true;
    }

    //提交答案
    public static function addQuestion($data) {
        $answer = $data['answer'] ?? '';
        //$token = isset($_COOKIE['token_key']) ? $_COOKIE['token_key'] : '';
        $userInfo = UserService::$userInfo;
        $quest = XmCQuestion::findOne(['id' => $data['q_id']])->toArray();
        if (empty($quest)) {
            return self::error(0, "该答卷不存在!");
        }
        if ($quest['type'] == self::SINGLE_CHECK) {
            $ans = [$answer];

        } else {
            $ans = explode(',', $answer);

        }
        ExamService::saveReportExamQuestion($data['rep_id'], $userInfo['uid'],$data['e_id'], $data['t_id'], $data['q_id'], $ans);
        return true;


        /**
        $content = json_decode($quest['content'], true);
        $ti = time();
        foreach ($content as $key => $val) {
            $score = 0;
            $is_right = 0;
            $right_num = 0;
            if ($quest['type'] == self::SINGLE_CHECK) {
                if ($val['is_r']) {
                    if ($val['n'] == $answer) {
                        $score = $quest['s'] ?? $quest['score'];
                        $is_right = 1;
                        $right_num = 1;
                        break;
                    }
                }
            } else {
                $ans = explode(',', $answer);
                foreach ($ans as $key => $val) {
                    if (!isset($quest[$key])) {
                        continue;
                    }
                    if ($val == $quest[$key]) {
                        $score += $quest['s'] ?? 0;
                        $right_num += 1;
                    }
                }
                if ($right_num == count($ans)) {
                    $is_right = 1;
                }
            }
        }

        //先判断是新增还是修改
        $question = XmReportExamQuestion::findOne(['report_id' => $data['rep_id'], 'exam_id' => $data['e_id'], 't_id' => $data['t_id'], 'q_id' => $data['q_id']]);
        if (empty($question)) {
            $question = new XmReportExamQuestion();
            $question->user_id = $userInfo['uid'] ?? 0;
            $question->report_id = $data['rep_id'];
            $question->exam_id = $data['e_id'];
            $question->t_id = $data['t_id'] ?? 0;
            $question->q_id = $data['q_id'] ?? 0;
            $question->is_right = 0;
            $question->answer = '';
            $question->times = 0;
            $question->right_num = 0;
            $question->score = $score;
            $question->remark = '';
            $question->is_accept = 0;
            $question->status = 1;
            $question->add_time = $ti;
            $question->update_time = $ti;
            $question->save();

        } else {
            $question->answer = $answer;
            $question->times += $data['times'];
            $question->right_num = $right_num;
            $question->is_right = $is_right;
            $question->score = $score;
            $question->update_time = $ti;
            $question->save();
        }
        return true;
         * **/
    }

    /**
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function insertEmptyQuestion($data) {
        $ti = time();
        //$token = isset($_COOKIE['token_key']) ? $_COOKIE['token_key'] : '';
        //$userInfo = UserService::auth($token);
        $userInfo = UserService::$userInfo;
        if (isset($data['white_qids']) && !empty($data['white_qids'])) {
            $white_ids = explode(',', $data['white_qids']);
            if (!empty($white_ids)) {
                $inData = [];
                foreach ($white_ids as $k => $v) {
                    $t_q_id = explode('_', $v);
                    $inData[] = [
                        'user_id' => $userInfo['uid'] ?? 0,
                        'report_id' => $data['rep_id'],
                        'exam_id' => $data['e_id'],
                        't_id' => $t_q_id[0] ?? 0,
                        'q_id' => $t_q_id[1] ?? 0,
                        'is_right' => 0,
                        'answer' => '',
                        'times' => 0,
                        'right_num' => 0,
                        'score' => 0,
                        'remark' => '',
                        'is_accept' => 1,
                        'status' => 1,
                        'add_time' => $ti,
                        'update_time' => $ti
                    ];
                }
                if ($inData) {
                    Yii::$app->db->createCommand()->batchInsert(XmReportExamQuestion::tableName(),
                        ['user_id', 'report_id', 'exam_id', 't_id', 'q_id', 'is_right', 'answer', 'times', 'right_num',
                            'score', 'remark', 'is_accept', 'status', 'add_time', 'update_time'],
                        $inData)->execute();
                }
            }
        }

        return true;
    }

    public static function newRecord($data) {
        //先判断该试卷是否存在
        $exam = XmCExam::find()->where(['id' => $data['e_id'], 'status' => 1])->asArray()->one();
        //print_r($exam);exit;
        if (empty($exam)) {
            return self::error(0, "该试卷不存在！");
        }
        $userInfo = UserService::$userInfo;
        $ti = time();
        $rep = new XmReportExam();
        $rep->user_id = !empty($userInfo['uid']) ? $userInfo['uid'] : 0;
        $rep->exam_id = $data['e_id'];
        $rep->score = 0;
        $rep->correct_rate = 0;
        $rep->times = 0;
        $rep->complete_state = 0;
        $rep->add_time = $ti;
        $rep->update_time = $ti;
        $rep->status = 1;
        $rep->save();
        //echo $rep->id;exit;
        $da['rep_id'] = $rep->id;
        return $da;

    }



}
