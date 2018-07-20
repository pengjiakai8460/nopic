<?php

namespace Admin\service;

use common\models\orm\XmCQuestion;
use common\models\orm\XmCQuestionTags;
use common\models\orm\XmCTag;
use OSS\OssClient;
use OSS\Core\OssException;
use Yii;

/**
 * Content 试卷管理
 */
class QuestionService extends BaseService
{
    public static $quest_type = [
        1 => "选择题",
        2 => "问题求解",
        3 => "阅读程序写结果",
        4 => "完善程序"
    ];

    public static $select_type = 1; //选择题
    public static $solving_problem_type = 2; //问题求解
    public static $read_code_type = 3; //阅读程序写结果
    public static $perfect_type = 4; //完善程序

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return UsersManageService
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function getWhere($get)
    {
        $question = XmCQuestion::find();
        if (isset($get['qid']) && is_numeric($get['qid']) && !empty($get['qid'])) {
            $question = $question->andWhere(['id' => $get['qid']]);
        }

        if (isset($get['name']) && !empty($get['name'])) {
            $question = $question->andWhere(['like', 'qname', $get['name']]);
        }

        if (isset($get['qtype']) && is_numeric($get['qtype']) && !empty($get['qtype'])) {
            $question = $question->andWhere(['type' => $get['qtype']]);
        }

        if (isset($get['tag']) && is_numeric($get['tag']) && !empty($get['tag'])) {
            if($get['tag'] != 0){
                $tagsId = TagService::getchildrensByTagId($get['tag']) ?? '-1';
                $qidArr = XmCQuestionTags::find()->where(['in','tag_id',explode(',',$tagsId)])->andWhere(['status'=>1])->select('distinct(q_id)')->asArray()->all() ;
                $qidArr = $qidArr ? array_column($qidArr,'q_id') : [-1];
                $question = $question->andWhere(['in', 'id', $qidArr]);
            }
        }
        return $question;
    }

    public static function selectQuestion($pagestart = 0, $islock, $pageLength = 15, $or = null, $order = null, $type = 0, $get = [])
    {
        $question = self::getWhere($get);
//        echo $question->limit($pageLength)->offset($pagestart * $pageLength)->orderBy("id desc")->createCommand()->getRawSql();exit;
        $quest = $question->limit($pageLength)->offset($pagestart * $pageLength)->orderBy("id desc")->asArray()->all();
        if ($quest) {
            //获取知识点
            $questIdArr = array_column($quest,'id');
            $tagQidArr = XmCQuestionTags::find()->where(['in','q_id',$questIdArr])->andWhere(['status'=>1])->select('tag_id,q_id')->asArray()->all();
            //标签id
            $tagIdArr = [];
            //q_id 为key tag_id 为 value  ['问题id'=>[[标签1id],[标签2id]]]
            $formatTagQid = [];
            foreach ($tagQidArr as  $k=>$v){
                $tagIdArr[] = $v['tag_id'];
//                if(isset($formatTagQid[$v['q_id']])){
//                    $formatTagQid[$v['q_id']][] = $v['tag_id'];
//                }else{
//                    $formatTagQid[$v['q_id']][] = $v['tag_id'];
//                }
                $formatTagQid[$v['q_id']][] = $v['tag_id'];
            }
            $tagIdArr = $tagQidArr ? array_column($tagQidArr,'tag_id') : [-1];
            $tagData = XmCTag::find()->where(['in','id',$tagIdArr])->select('id,name')->asArray()->all();
            $tagData = $tagData ? array_column($tagData,'name','id') : [];


            foreach ($quest as $k => $v) {
                $title = strip_tags(htmlspecialchars_decode($v['title']));
                $quest[$k]['title'] = str_replace('&nbsp;', '', $title);
                $quest[$k]['type'] = self::$quest_type[$v['type']];
                $quest[$k]['stat'] = $v['status'] ? "正常" : "禁用";

                //添加知识点
                $quest[$k]['tag'] = '';
                if(isset($formatTagQid[$v['id']])){
                    foreach ($formatTagQid[$v['id']] as $row){
                        $quest[$k]['tag'] .=  $quest[$k]['tag'] ? ','.$tagData[$row] : $tagData[$row];
                    }
                }
            }

        }
        return $quest;
    }

    //获取所有问题标签
    public static function getAllTags()
    {
        $tags = XmCTag::find()->where("status=1")->asArray()->all();
        $t = array_column($tags, 'name', 'id');
        return $t;
    }

    //获取问题信息
    public static function getQuestionById($data)
    {
        $question = XmCQuestion::findOne(['id' => $data['id']]);
        if ($question) {

            $quest = $question->toArray();
            //获取其所有的标签
            $tags = XmCQuestionTags::find()->where(['q_id' => $data['id']])->asArray()->all();
            $quest['tags'] = [];
            if ($tags) {
                foreach ($tags as $key => $tag) {
                    TagService::$parents = false;
                    $quest['tags'][$tag['tag_id']] = TagService::getparentsNameByTagId($tag['tag_id'], '');
                }
            }
            $quest['cont'] = json_decode($question['content'], true);
            foreach ($quest['cont'] as $k => $v) {
                $quest['cont'][$k]['char'] = chr(65 + $k);
                $quest['cont'][$k]['c'] = htmlspecialchars_decode(stripcslashes($v['c']));
            }
            $quest['title'] = htmlspecialchars_decode(stripcslashes($quest['title']));
            $quest['explain'] = htmlspecialchars_decode(stripcslashes($quest['explain']));
            return $quest;
        } else {
            return [];
        }

    }

    public static function getCount($get)
    {
        $question = self::getWhere($get);
        $count = $question->count();
        return $count;
    }

    //添加题目
    public static function addQuest($data)
    {
        $ti = time();
        if (isset($data['qid']) && !empty($data['qid'])) {
            $quest = XmCQuestion::findOne(["id" => $data['qid']]);
            if (empty($quest)) {
                return false;
            }
        }else {
            $quest = new XmCQuestion();
            $quest ->add_time = $ti;
        }
        $quest->title = isset($data['title']) ? $data['title'] : '';
        $quest->qname = isset($data['qname']) ? $data['qname'] : '';
        $quest->type = isset($data['qtype']) ? $data['qtype'] : 1;
        $quest->from_type = isset($data['from_type']) ? $data['from_type'] : 0;
        $quest->answer_count = isset($data['answer_count']) ? $data['answer_count'] : 1;
        $quest->explain = isset($data['explain']) ? $data['explain'] : '';
        $quest->score = isset($data['score']) ? $data['score'] : 5;
        $quest->adder_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 1;
        $quest->complexity = isset($data['q_comp']) ? $data['q_comp'] : 1;
        $quest->update_time = $ti;
        if (isset($data['q_from']) && !empty($data['q_from'])) {
            $q_from = explode('|', $data['q_from']);
            $quest->from_type = $q_from[0];
            if ($q_from[0] == 1) {
                $quest->from_yearannual = isset($q_from[1]) ? $q_from[1] : 0;
            }
        }
        if ($data['qtype'] == self::$select_type) {
            //c:内容,n:前缀,is_r:是否是正确内容 s:分数
            $com = [];
            $data['q_select'] = isset($data['answer']) ? json_decode(htmlspecialchars_decode(stripcslashes($data['q_select'])), true) : "[]";
            foreach ($data['q_select'] as $k=>$v){
                $i = $k + 1;
                $com[$k]["n"] = chr(64 + $i);
                $com[$k]["c"] = $v['c'];
                $com[$k]["s"] = $data['score'] ? $data['score'] : '';
                $com[$k]["is_r"] = $v['is_r'];
            }
            $quest->content = json_encode($com);
        } else {
            $com = [];
            $data['answer'] = isset($data['answer']) ? json_decode(htmlspecialchars_decode(stripcslashes($data['answer'])), true) : "[]";
            foreach ($data['answer'] as $row) {
                $row['is_r'] = 1;
                $row['n'] = '';
                $com[] = $row;
            }
            $quest->content = json_encode($com);
        }
        $ret = $quest->save();
        //先批量删除旧的标签
        XmCQuestionTags::deleteAll(['q_id' => $data['qid']]);
        if ($ret && isset($data['q_tag']) && !empty($data['q_tag'])) {
            $tags = explode('|', $data['q_tag']);

            $das = [];
            foreach ($tags as $k => $v) {
                $das[$k]['q_id'] = $quest->id;
                $das[$k]['tag_id'] = $v;
                $das[$k]['adder_id'] = isset($_SESSION['uid']) ? $_SESSION['uid'] : 1;;
                $das[$k]['status'] = 1;
                $das[$k]['add_time'] = $ti;
                $das[$k]['update_time'] = $ti;
            }
            Yii::$app->db->createCommand()->batchInsert(XmCQuestionTags::tableName(), ['q_id', 'tag_id', 'adder_id', 'status', 'add_time', 'update_time'], $das)->execute();
        }
        return $ret;
    }

    //删除题目
    public static function delQuestion($data)
    {
        $q = XmCQuestion::findOne(["id" => $data['id']]);
        if ($q) {
            $ti = time();
            if ($data['status']) {
                $q->status = 0;

            } else {
                $q->status = 1;
            }
            $q->update_time = $ti;
            $r = $q->save();
            if ($r) {
                $st['code'] = 0;
                $st['msg'] = "修改成功!";
                $st['data'] = [];
            } else {
                $st['code'] = 1;
                $st['msg'] = "修改失败,请重新修改!";
                $st['data'] = [];
            }
        } else {
            $st['code'] = 1;
            $st['msg'] = "该题目不存在,请确认!";
            $st['data'] = [];
        }
        return $st;

    }

    //上传图片到oss
    public static function upload($fileInfo) {
        //require_once '../../vendor/aliyuncs/oss-sdk-php/autoload.php';
        $accessKeyId = env("AccessKeyId");
        $accessKeySecret = env("AccessKeySecret");
        $endpoint = env("Endpoint");
        $bucket = env("Bucket");
        $object = "Uploads/xmsj/cpp/" . $fileInfo['title'];
        $file = $fileInfo['url'];
        $options = array();
        $ret = [];
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false );
            $ret = $ossClient->uploadFile($bucket, $object, $file, $options);
        } catch (OssException $e) {
            printf($e->getMessage() . "\n");
            return;
        }

        return $ret;
    }

    //更新题目
    public static function update() {
        $quest = XmCQuestion::find()->where(['id' => 940])->asArray()->one();
        echo htmlspecialchars_decode($quest['title']);exit;

        print_r(json_encode($quest));exit;
    }



}