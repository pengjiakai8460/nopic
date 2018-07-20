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
use common\models\orm\XmCFeedback;

use Admin\service\QuestionService;

/**
 * Content 每日任务管理
 */
class FeedbackService extends BaseService
{


    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return UsersManageService
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
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
        $xmCExam = XmCFeedback::find();
        $xmCExam->where(['status' => 1]);
        if (is_array($where) && $where) {
            foreach ($where as $row) {
                $xmCExam->andWhere($row);
            }
        }
        $res = $xmCExam->limit($pageLength)->offset($pagestart * $pageLength)->orderBy("id desc")->asArray()->all();

        //遍历反馈内容
        foreach ($res as $k => $v) {
            $res[$k]['url'] = '';
            if (isset($res[$k]['extra'])) {
                $u = json_decode($v['extra'], true);
                $res[$k]['url'] = isset($u['url']) ? $u['url'] : '';
                $res[$k]['question_id'] = isset($u['question_id']) ? $u['question_id'] : '';
            }

        }

        return $res;
    }

    /**
     * 获取每日任务的数量
     * @param $where
     * @return int|string
     */
    public static function getCount($where)
    {
        $xmCExam = XmCFeedback::find();
        $xmCExam->where(['status' => 1]);
        if (is_array($where) && $where) {
            foreach ($where as $row) {
                $xmCExam->andWhere($row);
            }
        }
        $count = $xmCExam->count();
        return $count;
    }


}