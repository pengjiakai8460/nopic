<?php

namespace Admin\service;

use common\models\orm\XmCQuestionTags;
use Yii;
use Admin\service\BaseService;
use common\models\orm\XmCExam;
use common\models\orm\XmCExamTestlets;
use common\models\orm\XmCTestlets;
use common\models\orm\XmCTestletsQuestion;
use common\models\orm\XmCQuestion;
use common\models\orm\XmCQusetionTags;

use Admin\service\QuestionService;

/**
 * Content 每日任务管理
 */
class TaskService extends BaseService
{
    //2：问题求解，3：阅读程序写结果 4：完善程序
    public static $qusTypeName = [
        1 => '单选选择',
        2 => '问题求解',
        3 => '阅读程序写结果',
        4 => '完善程序',
    ];

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return UsersManageService
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function dosetStatus($id,$status){
        Yii::$app->db->createCommand()->update(XmCExam::tableName(), ['status' => $status], "id=:id", [
            ':id' => $id
        ])->execute();
    }

    public static function dosave($Data)
    {

        $taskData = self::doTaskave($Data);
        self::dosaveTestletQuestion($taskData);
        return $taskData;
    }

    public static function doTaskave($Data)
    {

        $Data['task_title'];
        $time = time();


        if (!isset($Data['task_id'])) {
            return false;
        }
        if (isset($Data['task_id']) && $Data['task_id']) {
            $examM = XmCExam::findOne($Data['task_id']);
        } else {
            $examM = new XmCExam();
            $examM->add_time = $time;
        }
        if ($examM) {
            $examM->title = $Data['task_title'];
            $examM->status = 1;
            $examM->update_time = $time;
            $examM->type = 3;
            $examM->all_score = 100;
            $examM->adder_id = $_SESSION['uid'];
            $examM->task_date = strtotime($Data['task_date']);
            $examM->save();
            $Data['task_id'] = $examM->id;
            $Data['add_time'] = $examM->add_time;
            $Data['update_time'] = $examM->update_time;
            return $Data;
        } else {
            return false;
        }
    }

    public static function dosaveTestletQuestion($data)
    {
        $time = time();
        $examTestlet = XmCExamTestlets::find()->where(['e_id'=>$data['task_id']])->andWhere(['status'=>1])->asArray()->one();
        if($examTestlet){
            $testletId = $examTestlet['t_id'];
        }else{
            $testletId = self::dosaveTestlet();
            $xmexamTestletsM = new XmCExamTestlets();
            $xmexamTestletsM->isNewRecord = true;
            $xmexamTestletsM->e_id = $data['task_id'];
            $xmexamTestletsM->t_id = $testletId;
            $xmexamTestletsM->adder_id = $_SESSION['uid'];
            $xmexamTestletsM->status = 1;
            $xmexamTestletsM->add_time = $time;
            $xmexamTestletsM->update_time = $time;
            $xmexamTestletsM->save();
        }


        $dbData = [];//数据库数据
        $postData = [];// 提交数据

        $insertData = [];//插入数据
        $updateData = [];//更新数据
        $deleteData = []; //删除数据
        $questionData = XmCTestletsQuestion::find()->where(['t_id' => $testletId])->select('q_id,sort')->asArray()->all();
        $dbData = array_column($questionData, 'q_id', 'sort');

        if ($data['qids']) {
            $saveqidsArr = explode(',', $data['qids']);
            foreach ($saveqidsArr as $k => $v) {
                $postData[$k + 1] = $v;
            }
        }
        // 1=>12 ,2=>15,3=>16,4=>17 db
        // 1=>12 ,3=>15,4=>16,5=>18 post

        $deleteData = array_diff($dbData, $postData); // 4=>17
        $insertData = array_diff($postData, $dbData); // 5=>18
        $sampleData = array_intersect($postData, $dbData); // 1=>12 3=>15,4=>16

        //如果 db与post 的key不一样
        foreach ($sampleData as $k => $v) {
            if ($dbData[$k] != $v) {
                $updateData[$k] = $v;
            }
        }
        $inData = [];
        foreach ($insertData as $k => $v) {
            $inData[] = [
                't_id' => $testletId,
                'q_id' => $v,
                'adder_id' => $_SESSION['uid'],
                'status' => 1,
                'sort' => $k,
                'add_time' => $time,
                'update_time' => $time
            ];
        }

        if ($inData) {
            $res = Yii::$app->db->createCommand()->batchInsert(XmCTestletsQuestion::tableName(), ['t_id', 'q_id', 'adder_id', 'status', 'sort', 'add_time', 'update_time'], $inData)->execute();
        }
        foreach ($deleteData as $k => $v) {
            Yii::$app->db->createCommand()->update(XmCTestletsQuestion::tableName(), ['status' => 0], "q_id=:q_id", [
                ':q_id' => $v
            ])->execute();

        }
        foreach ($updateData as $k => $v) {
            Yii::$app->db->createCommand()->update(XmCTestletsQuestion::tableName(), ['sort' => $k], "q_id=:q_id", [
                ':q_id' => $v
            ])->execute();
        }
    }

    public static function dosaveTestlet()
    {
        $time = time();
        $testletsM = new XmCTestlets();
        $testletsM->add_time = $time;
        $testletsM->title = date('YmdHis', $time) . '每日任务';
        $testletsM->type = 1;
        $testletsM->status = 1;
        $testletsM->update_time = $time;
        $testletsM->save();
        return $testletsM->id;
    }

    /**
     * 列出每日任务
     * @param int $pagestart
     * @param int $pageLength
     * @param null $where
     * @param null $order
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($pagestart = 0, $pageLength = 15, $where = null, $order = null)
    {
        $xmCExam = XmCExam::find();
        $xmCExam->where(['type' => 3]);
        if (is_array($where) && $where) {
            foreach ($where as $row) {
                $xmCExam->andWhere($row);
            }
        }
        $res = $xmCExam->limit($pageLength)->offset($pagestart * $pageLength)->orderBy("id desc")->asArray()->all();

        return $res;
    }

    /**
     * 获取每日任务的数量
     * @param $where
     * @return int|string
     */
    public static function getCount($where)
    {
        $xmCExam = XmCExam::find();
        $xmCExam->where(['type' => 3]);
        if (is_array($where) && $where) {
            foreach ($where as $row) {
                $xmCExam->andWhere($row);
            }
        }
        $count = $xmCExam->count();
        return $count;
    }

    /**
     * 获取组卷详情
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getExamDetail($id)
    {
        return XmCExam::find()->where(['id' => $id])->asArray()->one();
    }

    /**
     * 根据组卷id 得到 对应的 问题分类数量 以及 所有问题分类的数量
     * type1,2,3,4对应所有四种类型的问题数量
     * mytype1,2,3,4 对应组卷的四种类型的问题数量
     * @param $taskId
     * @return array
     */
    public static function getTaskQuestionCount($taskId)
    {
        $returnData = [
            'type1' => 0,
            'type2' => 0,
            'type3' => 0,
            'type4' => 0,
            'mytype1' => 0,
            'mytype2' => 0,
            'mytype3' => 0,
            'mytype4' => 0,
        ];

        if ($taskId) {
            $tIdArr = XmCExamTestlets::find()->where(['e_id' => $taskId])->select('t_id')->asArray()->all();
            $tIdArr = array_column($tIdArr, 't_id');

            if ($tIdArr) {
                $qIdArr = XmCTestletsQuestion::find()->where(['in', 't_id', $tIdArr])->andWhere(['status'=>1])->select('q_id')->asArray()->all();
                $qIdArr = array_column($qIdArr, 'q_id');
                if ($qIdArr) {
                    $myData = self::groupQuestion($qIdArr);
                    foreach ($myData as $k => $v) {
                        $tempType = 'mytype' . $v['type'];
                        $returnData[$tempType] = $v['count'];
                    }
                }
            }
        }

        $res = self::groupQuestion([]);
        foreach ($res as $k => $v) {
            $tempType = 'type' . $v['type'];
            $returnData[$tempType] = $v['count'];
        }
        return $returnData;
    }

    /**
     * 根据问题id 得到 问题分类
     * @param array $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function groupQuestion(array $id = [])
    {
        $where = $id ? ['in', 'id', $id] : [];
        return XmCQuestion::find()->select('count(type) as count,type')->where($where)->groupBy('type')->asArray()->all();
    }

    /**
     * 获取题组下的试题
     * @param $id
     * @return array|string|\yii\db\ActiveRecord[]
     */
    public static function getTestletsDetailByTaskId($id)
    {

        $examTestList = XmCExamTestlets::find()->where(['e_id' => $id, 'status' => 1])->asArray()->all();
        $testletId = array_column($examTestList, 't_id');
        if ($testletId) {
            //题组数据
            $testletData = XmCTestlets::find()->where(['in', 'id', $testletId])->andWhere(['=', 'status', 1])->asArray()->all();
            //题组与试题的关系数据
            $tqData = XmCTestletsQuestion::find()->where(['in', 't_id', $testletId])->andWhere(['=', 'status', 1])->asArray()->all();
            //试题的id 数组
            $qIdArr = array_column($tqData, 'q_id');
            //试题的排序
            $qIdSortArr = array_column($tqData, 'sort', 'q_id');

            //试题数据
            $qData = XmCQuestion::find()->where(['in', 'id', $qIdArr])->asArray()->all();


            foreach ($qData as $k => $v) {
                $qData[$k]['title'] = htmlspecialchars_decode(stripcslashes($v['title']));

                $qData[$k]['content'] = self::formatquescon(json_decode($v['content'], true));

                $qData[$k]['sort'] = $qIdSortArr[$v['id']];
            }

            array_multisort(array_column($qData, 'sort'), SORT_ASC, $qData);
            return $qData;
        } else {
            return '';
        }
    }


    public static function formatquescon($con)
    {

        if(is_array($con) && $con){
            foreach ($con as $k=>$v){
                $con[$k]['c'] = htmlspecialchars_decode($v['c']);
            }
        }
        return $con;
    }

    /**
     * ajax返回试题数据
     * @param $searchParam
     * @return mixed
     */
    public static function searchQuestion($searchParam)
    {

        //20180509 linm 问题查询 改为 union查询  当（问题几十万条时）量大的时候需要注意 需要优化
        $queryAll = (new \yii\db\Query());

        $query1 = (new \yii\db\Query())
            ->select("*")
            ->from(XmCQuestion::tableName())
            ->where(['status'=>1]);


        $query2 = (new \yii\db\Query())
            ->select("*")
            ->from(XmCQuestion::tableName())
            ->where(['status'=>1]);

        $searchParam['where'] = [];
        $searchParam['where'][] = ['status' => 1];
        if ($searchParam['tag_id']) {
            $tagIds = TagService::getchildrensByTagId($searchParam['tag_id']);

            $tagIds = explode(',',$tagIds);
            $qIdArr = XmCQuestionTags::find()->where(['in','tag_id',$tagIds])->select('q_id')->asArray()->all();
            if ($qIdArr) {
                $qIdArr = array_column($qIdArr, 'q_id');
//                $searchParam['where'][] = ['in', 'id', $qIdArr];
                $query1->andWhere(['in', 'id', $qIdArr]);
                $query2->andWhere(['in', 'id', $qIdArr]);
            } else {
//                $searchParam['where'][] = ['id' => -1];
                $query1->andWhere(['id' => -1]);
                $query2->andWhere(['id' => -1]);
            }
        }

        if ($searchParam['type']) {
//            $searchParam['where'][] = ['type' => $searchParam['type']];
            $query1->andWhere(['type' => $searchParam['type']]);
            $query2->andWhere(['type' => $searchParam['type']]);
        }

        if ($searchParam['searchkey']) {
//            $searchParam['where'][] = ['or', ['=', 'id', $searchParam['searchkey']], ['like', 'qname', $searchParam['searchkey']]];
            if ($searchParam['searchqtype'] == 1) {
                $query1->andWhere(['=', 'id', $searchParam['searchkey']]);
                $query2->andWhere(['=', 'id', $searchParam['searchkey']]);
            }
            if ($searchParam['searchqtype'] == 2) {
                $query1->andWhere(['=', 'qname', $searchParam['searchkey']]);
                $query2->andWhere(['=', 'qname', $searchParam['searchkey']]);
            }
            if ($searchParam['searchqtype'] == 3) {
                $query1->andWhere(['>=', 'add_time', strtotime($searchParam['searchkey'])]);
                $query2->andWhere(['>=', 'add_time', strtotime($searchParam['searchkey'])]);
            }
            if($searchParam['searchqtype'] == 0) {
                $query1->andWhere(['or', ['=', 'id', $searchParam['searchkey']], ['like', 'qname', $searchParam['searchkey']]]);
                $query2->andWhere(['or', ['=', 'id', $searchParam['searchkey']], ['like', 'qname', $searchParam['searchkey']]]);
            }
        }

        if ($searchParam['q_id']) {
            $query1->andWhere(['in','id',$searchParam['q_id']]);
            $query2->andWhere(['not in','id',$searchParam['q_id']]);
        }
        $query2->orderBy('id desc');

        $queryAll -> from(['temp'=>$query1->union($query2)]);

        $questionData['count'] = $queryAll->count();
        $questionData['list'] = $queryAll->limit($searchParam['pagelength'])->offset($searchParam['pageno'] * $searchParam['pagelength'])->all();

        //$searchParam['pagelength']
//        $quesM = XmCQuestion::find();
//        if(is_array($searchParam['where']) && $searchParam['where']){
//            foreach ($searchParam['where'] as  $row){
//                $quesM->andWhere($row);
//            }
//        }

        //

//        $questionData['list'] = $quesM->limit($searchParam['pagelength'])->offset($searchParam['pageno'] * $searchParam['pagelength'])
//            ->orderBy($searchParam['order'])
//            ->asArray()
//            ->all();
//        $clone1 = clone $quesM;
//        $sql = $clone1->createCommand()->getRawSql();
//        var_dump($sql);
//        $questionData['count'] = $quesM->count();
//        $questionData = self::commonlist(XmCQuestion::find(), $searchParam['pageno'], $searchParam['pagelength'], $searchParam['where'], $searchParam['order']);

        if (!empty($questionData['list'])) {

            foreach ($questionData['list'] as $k => $v) {
                $questionData['list'][$k]['title'] = htmlspecialchars_decode(stripcslashes($v['title']));
                $questionData['list'][$k]['explain'] = htmlspecialchars_decode(stripcslashes($v['explain']));
                $questionData['list'][$k]['qtypename'] = self::$qusTypeName[$v['type']];
                $tempcontent = json_decode($v['content'], true);

                if (is_array($tempcontent) && $tempcontent) {
                    foreach ($tempcontent as $key => $value) {
                        $tempcontent[$key]['c'] = htmlspecialchars_decode($value['c']);
                        if ($value['is_r']) {
                            switch ($v['type']){
                                case 1:
                                    $questionData['list'][$k]['ans'] = $value['n'];
                                    break;
                                default:
                                    $questionData['list'][$k]['ans'] = (isset($questionData['list'][$k]['ans']) ? $questionData['list'][$k]['ans'] : '' ) ."". ($key+1).'.' .$value['c']."\r\n";
                            }

                        }
                    }
                    $questionData['list'][$k]['content'] = $tempcontent;
                } else {
                    $questionData['list'][$k]['content'] = [];
                }
            }
//            var_dump($questionData['list']);exit();
        }
        return $questionData;
    }
}