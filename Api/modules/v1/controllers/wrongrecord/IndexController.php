<?php

namespace Api\modules\v1\controllers\wrongrecord;


use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\WrongRecordService;

class IndexController extends ApiBaseController
{
    /**
     * 错题记录api接口
     * @return array
     */
    /**
     * @api {get} wrongrecord/index/list List
     * @apiDescription 错题记录api接口
     * @apiVersion 1.0.0
     * @apiName List
     * @apiGroup wrongrecord
     * @apiParam {Number} r_id  下拉刷新加载
     * @apiParam {Number} qtype    问题类型 1单项选择 2问题求解 3阅读程序写结果 4完善程序
     * @apiParam {Number} errtype    错误原因 1 计算错误 2 常识记忆错误 3算法错误 4 开发性题型错误 5其他
     * @apiParam {Number} errcount   错误次数
     * @apiSuccess {String} code 200
     * @apiSuccess {String} message
     * @apiSuccess {String} data 返回数据
     * @apiSuccess {String} timestamp 当前时间戳
     * @apiSuccessExample Success-Response:
     *
    {
    "code": 200,
    "message": "success",
    "data": {
        "wrong_record": //错误记录
        [
            {
                "id": "44",   //问题id
                "title": "在关系数据库中，存放在数据库中的数据的逻辑结构以（ ）为主", //问题标题
                "content": [ //问题内容
                    {
                    "n": "A", //选择
                    "c": "二叉树", //内容
                    "s": "5", //分数
                    "is_r": 0 //是否正确
                    },
                ],
                "explain": "二维表", //解析
                "type": "4", //问题类型 1单项选择 2问题求解 3阅读程序写结果 4完善程序
                "sort": 0,  //排序
                "answer": "D", // 答案 选择题是字符串 填空题是数组
                "myanswer": "", //我的答案
                "tagData": [ //知识点标签
                    {
                    "tag_id": "156",
                    "tag_name": "二叉堆的维护"
                    },
                ],
                "exam_title": [
                    "2017年真题" //关联记录历史
                ],
                "remark": "adsf", //备注
                "error_count": "3"//错误次数
                "error_type": "0", //1 计算错误 2 常识记忆错误 3算法错误 4 开发性题型错误 5其他
                "r_id": "56" //记录id
            },
            {
                "id": "46",
                "title": " 测试填空题",
                "content": [
                    {
                    "c": "i++",
                    "s": "2.5",
                    "is_r": 1,
                    "n": ""
                    },
                ],
                "explain": "测试填空题解析",
                "sort": 0,
                "answer": [
                    " break",
                    "t%50==0",
                    "a-p*b （或 a-b*p）",
                    "c*10+1 （或 10*c+1）",
                    "--n "
                ],
                "myanswer": "",
                "tagData": [
                    {
                    "tag_id": "144",
                    "tag_name": "完善程序"
                    }
                ],
                "exam_title": [
                    "2017年真题"
                ],
                "remark": "adsf", //备注
                "error_count": "3"//错误次数
                "error_type": "0", //1 计算错误 2 常识记忆错误 3算法错误 4 开发性题型错误 5其他
                "r_id": "56" //记录id
            }
        ]
    },
    "timestamp": 1523960995
    }
     */
    public function actionList()
    {
        $get = \Yii::$app->request->get();
        $data = WrongRecordService::wrongrecordList($get);
        return $this->success($data);
    }

    /**
     * 错题统计
     * @return array
     */
    /**
     * @api {get} wrongrecord/index/calcu Calcu
     * @apiDescription 错题统计接口
     * @apiVersion 1.0.0
     * @apiVersion 1.0.0
     * @apiName Calcu
     * @apiGroup wrongrecord
     * @apiSuccessExample Success-Response:
        {
            "code": 200,
            "message": "success",
            "data": {
                "wrong_tag": [   //错误标签
                    {
                    "id": "1", //标签id
                    "name": "计算机基本常识",
                    "wrong_count": 0 //错误次数
                    },
                ],
                "wrong_type": [ //错误标签
                    {
                    "type": 1, //问题类型
                    "name": "单选选择",
                    "wrong_count": 0 //错误次数
                    },
                ]
                "correct_rate": " 5", //正确率
     *          "average_rate": "50" //平均正确率
            },
            "timestamp": 1524647416
        }
     * */
    public function actionCalcu()
    {
        $data = WrongRecordService::getWrongCalCu();
        return $this->success($data);
    }

    /**
     * 提交备注接口
     * @return array
     */
    /**
      * @api {get} wrongrecord/index/remark Remark
      * @apiDescription 错误记录备注保存接口
      * @apiVersion 1.0.0
      * @apiVersion 1.0.0
      * @apiName Remark
      * @apiGroup wrongrecord
     * @apiParam {Number} q_id 题目ID
     * @apiParam {String} remark 备注
      * @apiSuccessExample Success-Response:
      * {
         "code": 200,
         "message": "success",
         "data": {},
         "timestamp": 1523959997
         }
      * */
    public function actionRemark()
    {
        $post = \Yii::$app->request->post();
        $rules = [
            [['q_id'], 'integer'],
            ['q_id', 'required'],
        ];
        $this->validate($post, $rules);
        WrongRecordService::setRemark($post);
        return $this->success();
    }

    /**
     * @api {get} wrongrecord/index/error-type Error-type
     * @apiDescription 错误类型设置口
     * @apiVersion 1.0.0
     * @apiVersion 1.0.0
     * @apiName Error-type
     * @apiGroup wrongrecord
     * @apiParam {Number} q_id 题目ID
     * @apiParam {Number} error_type 错误类型1 计算错误 2 常识记忆错误 3算法错误 4 开发性题型错误 5其他
     * @apiSuccessExample Success-Response:
     * {
        "code": 200,
        "message": "success",
        "data": {},
        "timestamp": 1523959997
        }
     * */
    public function actionErrorType()
    {
        $post = \Yii::$app->request->post();
        $rules = [
            [['q_id','error_type'], 'integer'],
            [['q_id','error_type'], 'required'],
        ];
        $this->validate($post, $rules);
        WrongRecordService::setErrorType($post);
        return $this->success();
    }

}