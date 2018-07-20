<?php
namespace Admin\modules\exams\controllers;

use Admin\modules\AdminController;
use Admin\modules\exams\Exams;
use Admin\service\ExamsService;
use common\models\orm\XmCExamTestlets;
use common\models\utils\FuncUtil;
use yii\data\Pagination;

class ExamController extends AdminController
{
    const PAGE_PER_NUMBER = 10;      // 15

    // 访问控制，暂时为空
    public function behaviors()
    {
        return [

        ];
    }

    //套题列表
    public function actionIndexList()
    {

        $get = FuncUtil::parseData(\Yii::$app->request->get());//过滤参数
        $where = array();
        if (!empty($get['title'])) {
            $where['title'] = $get['title'];
        }
        if (!empty($get['type'])) {
            $where['type'] = $get['type'];
        }
        if (!empty($get['id'])) {
            $where['id'] = $get['id'];
        }
        $page = !empty($get['page']) ? $get['page'] : 1;

        $list = ExamsService::getAllExamsList($where, self::PAGE_PER_NUMBER, $page);
        $pi = new Pagination(['totalCount' => $list['count'], 'pageSize' => self::PAGE_PER_NUMBER]);//分页
        $result = array();
        $result['searchKeys'] = $where;
        $result['count'] = $list['count'];
        $result['lists'] = $list['list'];
        $result['pageCount'] = $pi->pageCount;
        $result['pagination'] = $pi;
        $result['islocked'] = false;
        return $this->render('index.twig', $result);
    }

    /**
     * @return bool
     */
    public function actionChangeStatus()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        if (is_numeric($id) && !empty($id)) {
            return ExamsService::changeExamStatus($id);
        }
    }

    /**
     * 渲染创建套题页面
     */
    public function actionCreateExam()
    {
        return $this->render('create2.twig');
    }

    //创建试卷的行为方法
    public function actionAddExam()
    {
        $post = FuncUtil::parseData(\Yii::$app->request->post());
        $title = $post['exam_title'];
        $type = $post['exam_type'];
        $all_times = $post['exam_all_times'];
        $all_score = $post['exam_all_score'];
        $year = $post['exam_year'];
        $complexity = $post['exam_complexity'];
//        $testlet_sub = 0;
//        $question_sub = 0;
//        if(!empty($post['exam_testlet_data'])){
//            foreach($post['exam_testlet_data'] as $key=>$value){
//                $addData['testlet'][$testlet_sub]['title'] = $value['testlet_title'];
//                $addData['testlet'][$testlet_sub]['subtitle'] = $value['testlet_subtitle'];
//                $addData['testlet'][$testlet_sub]['type'] = $value['testlet_sort'];
//                if(empty($value['questions'])){
//                    continue;
//                }
//                foreach($value['questions'] as $k => $v){
//                    $addData['testlet'][$testlet_sub]['questions'][$question_sub]['id'] = $v['id'];
//                    $question_sub++;
//                }
//                $testlet_sub++;
//            }
//        }
        $ret = ExamsService::addExam($title, $type, $complexity ,$year, $all_score, $all_times);
        return $this->ajaxReturn($ret);
    }


    //获取多条题目的基本信息
    public function actionGetQuestions(){
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $id_arr = explode(',', $get['id_str']);
        $ret = array();
        foreach($id_arr as $key=>$value){
            $xuan_content = array();
            $arr = ExamsService::getQuestion($value);
            $arr['title'] = html_entity_decode($arr['title']);
            $arr['content'] = json_decode($arr['content']);
            foreach($arr['content'] as $k => $v){
                $xuan_content[$v->n] = html_entity_decode($v->c);
            }
            $arr['content'] = $xuan_content;
            $ret[] = $arr;
        }
        return $this->ajaxReturn($ret);
    }

    //渲染试卷所有题组信息（暂时放弃）
//    public function actionShowTestlets()
//    {
//        $get = FuncUtil::parseData(\Yii::$app->request->get());
//        $exam_id = $get['id'];
//        //获取对应题组信息
//        $testlets = ExamsService::getAllTestlet($exam_id);
//        array_multisort(array_column($testlets,'sort'),SORT_ASC,$testlets);
//        //试卷基本信息
//        $exam = ExamsService::getExam($exam_id);
//        $ret = [
//            'testlets'=>$testlets,
//            'exam' => $exam
//        ];
//        return $this->render('testlets2.twig',$ret);
//    }

    //替代actionShowTestlets
    public function actionShowTestlets()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $exam_id = $get['id'];
        $exam = ExamsService::getExam($exam_id);
        $examDetail = ExamsService::getCompleteExam($exam_id);
        $ret = ['exam'=>$exam,'details'=>$examDetail];

        return $this->render('testlets2.twig', $ret);
    }
    //获取试卷下所有题组和题目的二维数组数据
    public function actionExamDetail()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $exam_id = $get['exam_id'];
        $examDetail = ExamsService::getCompleteExam($exam_id);
        return $this->ajaxReturn($examDetail);
    }


    //变更题组信息
    public function actionUpdateTestlet()
    {
        $post = FuncUtil::parseData(\Yii::$app->request->get());
        $exam_id = $post['exam_id'];
        $testlet_id = $post['testlet_id'];
        $sort = $post['sort'];
        $title = $post['title'];
        $subtitle = $post['subtitle'];
        ExamsService::updateTestlet($exam_id, $testlet_id, $title, $subtitle, $sort);
    }

    //题组内题目列表
    public function actionTestletQuestions()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $testlet_id = $get['t_id'];
        $exam_id = $get['e_id'];
        $testlet = ExamsService::getTestlet($testlet_id);
        $exam = ExamsService::getExam($exam_id);
        return $this->render('questions.twig', ['t_id'=>$testlet_id, 'testlet'=>$testlet, 'exam'=>$exam]);
    }

    //获取题组中的信息
    public function actionGetTestletQuestions()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $testlet_id = $get['id'];
        //获取题组中的题目列表
        $questions = ExamsService::getTestletQuestions($testlet_id);
        foreach ($questions as $key=>$value){
            $xuan_content = array();
            $arr = json_decode($value['content']);
            foreach($arr as $k => $v){
                $xuan_content[$v->n] = html_entity_decode($v->c);
            }
            $questions[$key]['content'] = $xuan_content;
            $questions[$key]['title'] = html_entity_decode($value['title']);
        }
        return $this->ajaxReturn($questions);
    }
    
    //移除题组中的题目(废弃)
//    public function actionDelTestletQuestion()
//    {
//        $get = FuncUtil::parseData(\Yii::$app->request->get());
//        $t_q_id = $get['t_q_id'];
//        ExamsService::delTestletQuestion($t_q_id);
//    }

    public function actionDelQuestion()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        ExamsService::delQuestion($get['t_id'], $get['q_id']);
    }

    //变更排序(废弃)
//    public function actionUpTestletQuestionSort()
//    {
//        $get = FuncUtil::parseData(\Yii::$app->request->get());
//        $last_id = $get['last_t_q_id'];
//        $t_q_id = $get['t_q_id'];
//
//        ExamsService::upTestletQuestionSort($last_id, $t_q_id);
//    }

    //添加单个题目到题组中去
    public function actionAddQuestion()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        ExamsService::addQuestion($get['t_id'], $get['q_id']);
    }

    //添加题目到题组（废弃）
//    public function actionAddQuestion()
//    {
//        $get = FuncUtil::parseData(\Yii::$app->request->get());
//        $testlet_id = $get['testlet_id'];
//        $q_arr = $get['q_arr'];
//        $sort = $get['sort'];
//        foreach($q_arr as $q){
//            $sort++;
//            ExamsService::addTestletQuestion($testlet_id, $q, $sort);
//        }
//    }
    
    //添加题组并放到指定的套卷下
    public function actionAddTestlet()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $title = $get['title'];
        $subtitle = $get['subtitle'];
        $sort = $get['sort'];
        $exam_id = $get['exam_id'];
        $t_id = ExamsService::addTestlet($title, $subtitle);
        return ExamsService::addExamTestlet($exam_id,$t_id, $sort);
    }

    //变更试卷基本信息
    public function actionEditExam()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $exam_id = $get['exam_id'];
        $data = $get['data'];
        $ret = ExamsService::updateExam($exam_id, $data);
        return $this->ajaxReturn($ret);
    }

    //从试卷中删除题组
    public function actionDelExamTestlet()
    {
        $get = FuncUtil::parseData(\Yii::$app->request->get());
        $e_id = $get['e_id'];
        $t_id = $get['t_id'];
        ExamsService::delExamTestlet($e_id, $t_id);
    }

    //自动生成试卷
    public function actionAutogeneration() {
        //$get = FuncUtil::parseData(\Yii::$app->request->get());
        return $this->render('autogeneration.twig');

    }

    //自动根据专项生成专项试卷
    public function actionAutosaveexam() {
        $get = \Yii::$app->request->get();
        $type = empty($get['type']) ? 0 : $get['type'];
        $limit = empty($get['limit']) ? 0 : $get['limit'];
        $ret = ExamsService::autoSave($type, $limit);
        if($ret == 0){
            return $this->ajaxReturn('', '生成失败!', 'fail');
        }
        return $this->ajaxReturn('', '生成成功!', 'success');
    }

    //自动根据知识点标签生成试卷
    public function actionAutosavebytag() {
        $get = \Yii::$app->request->get();
        $tid = empty($get['tid']) ? 0 : $get['tid'];
        $ret = ExamsService::autoSaveByTid($tid);
        if($ret == 0){
            return $this->ajaxReturn('', '生成失败!', 'fail');
        }
        return $this->ajaxReturn('', '生成成功!', 'success');

    }
}