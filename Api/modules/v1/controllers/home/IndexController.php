<?php

namespace Api\modules\v1\controllers\home;

use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\UserService;
use Yii;

class IndexController extends ApiBaseController
{
    /**
     * 首页数据
     * @return array
     */
    /**
     * @api {get} home/index/list List
     * @apiDescription 首页接口数据
     * @apiVersion 1.0.0
     * @apiName List
     * @apiGroup Home
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
                    "uid": 1, //用户id
                    "last_login_time": "2018-04-25 00:00", //上次登录时间
                    "login_day": 3, //登录天数
                    "autograph": null, //签名
                    "nickname": "旋凯", //昵称
                    "img": "http://oss.xiaoma.wang/Public/Scratch/Scrachxin/image/xiaoma.png",//头像
                    "is_vip": 1,   //是否是vip 1是 0 不是
                    "vip_day": 79, //vip过期时间
                    ""
                    "price":{
                            "MONTH_PRICE": "19.9", //vip 每月价格
                            "YEAR_PRICE": "199" //vip 每年价格
                        },
                    "q_count": 16, //联系数量
                    "capa": { //能力指数 有6个
                        "1": {//知识点标签id
                            "name": "计算机基本常识", //
                            "right_count": 0, //答对 对应知识点标签的题目数量
                            "all_count": 3, //所有 对应知识点标签的题目数量
                            "score": 0  //指数值
                        },
                    },
                    "correct_rate": 16 //正确率
                    "over_rate": " 3" //排名率
            },
            "timestamp": 1524642986
        }
     */
    public function actionList()
    {
        $userInfo = UserService::getHomePageData(UserService::$userInfo);
        return $this->success($userInfo);
    }

    /**
     * 历史正确率接口
     * @return array
     */
    /**
     * @api {get} home/index/history-rate history-rate
     * @apiDescription 历史正确率接口
     * @apiVersion 1.0.0
     * @apiName history-rate
     * @apiGroup Home
     * @apiParam {String} search[非必填]  week 获取一周 month 获取一月 year 获取一年 默认一周
     * @apiParam {String} start_date[非必填]  开始时间 格式 20180424
     * @apiParam {String} end_date[非必填]  开始时间 格式 20180425
     * @apiSuccess {String} code 200
     * @apiSuccess {String} message
     * @apiSuccess {String} data 返回数据
     * @apiSuccess {String} timestamp 当前时间戳
     * @apiSuccessExample Success-Response:
     *
        {
        "code": 200,
        "message": "success",
        "data": [
            {
                "id": "11", //记录id
                "user_id": "1", //用户id
                "correct_rate": "7", //正确率 %
                "calcu_date": "20180425" //时间
            },
            {
                "id": "12",
                "user_id": "1",
                "correct_rate": "15",
                "calcu_date": "20180425"
            },
            {
                "id": "16",
                "user_id": "1",
                "correct_rate": "7",
                "calcu_date": "20180424"
            },
            {
                "id": "18",
                "user_id": "1",
                "correct_rate": "3",
                "calcu_date": "20180424"
            },
            {
                "id": "23",
                "user_id": "1",
                "correct_rate": "3",
                "calcu_date": "20180423"
            }
        ],
        "timestamp": 1524642986
        }
     */
    public function actionHistoryRate()
    {
        $get = \Yii::$app->request->get();
        $data = UserService::getHistoryRate($get);
        return $this->success($data);
    }
}