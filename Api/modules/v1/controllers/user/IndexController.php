<?php

namespace Api\modules\v1\controllers\user;

use Admin\service\QuestionService;
use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\UserService;
use Yii;

class IndexController extends ApiBaseController
{

    public function actionLogin()
    {
        $data = Yii::$app->getRequest()->post();
        $data['token'] = isset($_COOKIE['token']) ? $_COOKIE['token'] : '';
        $rules = [
            [['username', 'pwd'], 'required'],
            [['username', 'pwd'], 'string'],
        ];
        $this->validate($data, $rules);
        $data = UserService::login(trim($data['token']), trim($data['username']), trim($data['pwd']));
        return $this->success($data);
    }

    public function actionLoginout() {
        //$data = Yii::$app->getRequest()->post();
        $data = $_COOKIE;
        $rules = [
            [['token'], 'required'],
            [['token'], 'string'],
        ];
        $this->validate($data, $rules);
        $data = UserService::Loginout($data['token']);
        return $this->success($data);
    }

    public function actionAuth() {
        $data = $_COOKIE;
        $rules = [
            [['token'], 'required'],
            [['token'], 'string'],
        ];
        $this->validate($data, $rules);
        $data = UserService::auth($data['token']);
        return $this->success($data);
    }

    //判断用户是否支付成功
    public function actionPayresult() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['oid'], 'required'],
            [['oid'], 'string'],
        ];
        $this->validate($data, $rules);
        $data = UserService::payResult($data);
        return $this->success($data);

    }

    //判断用户是否是VIP，如果是VIP给出剩余天数
    /**
     * @api {post} user/inex/vipdetails  Index Vipdetails
     * @apiVersion 1.0.0
     * @apiName Index Vipdetails
     * @apiGroup User
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": {
    "is_vip": 1,
    "end_time": "1523543848"
    },
    "timestamp": 1525401249
    }
     */
    public function actionVipdetails() {
        $data = UserService::vipDetails();
        return $this->success($data);


    }

    /**
     * @api {get} user/index/feedback  Index Feedback
     * @apiVersion 1.0.0
     * @apiName Index Feedback
     * @apiGroup User
     *
     * @apiParam {String} title 标题
     * @apiParam {String} type 类型
     * @apiParam {String} content 意见反馈内容
     * @apiParam {String} extra  额外信息
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": {
    "ret": 1
    },
    "timestamp": 1525678781
    }
     *
     * @apiError RecordIdNotFound The id of the record can not empty.
     *
     * @apiErrorExample Error-Response:
     * {
    "code": 10034,
    "message": "Content只能包含至多500个字符。",
    "data": {},
    "timestamp": 1525678583
    }
     */
    public function actionFeedback() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['content', 'extra', 'title', 'type'], 'required'],
            [['content'], 'string', 'length' => [20, 500]],
            [['extra', 'title', 'type'], 'string'],
        ];
        $this->validate($data, $rules);
        $ret = UserService::feedback($data);
        return $this->success($ret);
    }

    //更新用户题库数据
    public function actionUpdateq() {
        QuestionService::update();
    }

    /**
     * @api {post} user/index/checkphone  Index Checkphone
     * @apiVersion 1.0.0
     * @apiName Index Checkphone
     * @apiGroup User
     *
     * @apiParam {String} phone 手机号
     * @apiParam {Int} type 类型 1:找回密码  2:注册
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": true,
    "timestamp": 1525848711
    }
     *
     * @apiError Mobile phone number format error.
     *
     * @apiErrorExample Error-Response:
     * {
    "code": 10031,
    "message": "手机号格式错误",
    "data": {},
    "timestamp": 1525848784
    }
     */
    public function actionCheckphone(){
        $post = Yii::$app->getRequest()->post();
        $rules = [
            [['phone','type'], 'required'],
            [['phone'], 'string'],
            [['type'], 'integer'],
        ];
        $this->validate($post, $rules);
        $post['phone'] = trim($post['phone']);
        $post['type'] = trim($post['type']);
        if(!preg_match("/^1[34578]\d{9}$/", $post['phone'])){
            return $this->error(static::ERR_INVALID_MOBILE['code'], "手机号格式错误");
        }
        $ret = UserService::valiPhoneByType($post['phone'], $post['type']);
        return $this->success($ret);
    }

    /**
     * @api {post} user/index/getphonecode  Index Getphonecode
     * @apiVersion 1.0.0
     * @apiName Index Getphonecode
     * @apiGroup User
     *
     * @apiParam {String} phone 手机号
     * @apiParam {Integer} type 类型 1:找回密码  2:注册
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": {
    "result": "ok",
    "info": "发送成功"
    },
    "timestamp": 1525852312
    }
     *
     * @apiError Mobile phone number format error.
     *
     * @apiErrorExample Error-Response:
     * {
    "code": 10031,
    "message": "手机号格式错误",
    "data": {},
    "timestamp": 1525848784
    }
     */
    public function actionGetphonecode() {
        $post = Yii::$app->getRequest()->post();
        $rules = [
            [['phone', 'type'], 'required'],
            [['phone'], 'string'],
            [['type'], 'integer'],
        ];
        $this->validate($post, $rules);
        $post['phone'] = trim($post['phone']);
        $post['type'] = trim($post['type']);
        if(!preg_match("/^1[34578]\d{9}$/", $post['phone'])){
            return $this->error(static::ERR_INVALID_MOBILE['code'], "手机号格式错误");
        }
        $userPhone = UserService::phoneCode($post['phone'], $post['type']);
        return $this->success($userPhone);
    }

    /**
     * @api {post} user/index/register  Index Register
     * @apiVersion 1.0.0
     * @apiName Index Register
     * @apiGroup User
     *
     * @apiParam {String} phone 手机号
     * @apiParam {String} pwd 密码
     * @apiParam {String} phonecode 验证码
     *
     *
     * @apiSuccessExample Success-Response:
     *
     *{
    "code": 200,
    "message": "success",
    "data": true,
    "timestamp": 1525858785
    }
     *
     * @apiError The cell phone number has already existed.
     *
     * @apiErrorExample Error-Response:
     *{
    "code": 0,
    "message": "该手机号已存在！",
    "data": {},
    "timestamp": 1525858715
    }
     */
    public function actionRegister() {
        $post = Yii::$app->getRequest()->post();
        $rules = [
            [['phone', 'pwd', 'phonecode'], 'required'],
            [['phone', 'phonecode'], 'string'],
            [['pwd'], 'string', 'length' => [6, 12]],
        ];
        $this->validate($post, $rules);
        $post['phone'] = trim($post['phone']);
        $post['pwd'] = trim($post['pwd']);
        $post['phonecode'] = trim($post['phonecode']);
        if(!preg_match("/^1[34578]\d{9}$/", $post['phone'])){
            return $this->error(static::ERR_INVALID_MOBILE['code'], "手机号格式错误");
        }
        $reg = UserService::register($post);
        return $this->success($reg);
    }

    /**
     * @api {post} user/index/forgetpwd  Index Forgetpwd
     * @apiVersion 1.0.0
     * @apiName Index Forgetpwd
     * @apiGroup User
     *
     * @apiParam {String} phone 手机号
     * @apiParam {String} phonecode 验证码
     * @apiParam {String} pwd1 密码1
     * @apiParam {String} pwd2 密码2
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": true,
    "timestamp": 1525914651
    }
     *
     * @apiError CodeError The errorcode is error..
     *
     * @apiErrorExample Error-Response:
     *{
    "code": 0,
    "message": "验证码错误！",
    "data": {},
    "timestamp": 1525914678
    }
     */
    public function actionForgetpwd() {
        $post = Yii::$app->getRequest()->post();
        $rules = [
            [['phone', 'phonecode', 'pwd1', 'pwd2'], 'required'],
            [['phone', 'phonecode'], 'string'],
            [['pwd1', 'pwd2'], 'string', 'length' => [6, 12]],
        ];
        $this->validate($post, $rules);
        $post['phone'] = trim($post['phone']);
        $post['phonecode'] = trim($post['phonecode']);
        $post['pwd1'] = trim($post['pwd1']);
        $post['pwd2'] = trim($post['pwd2']);
        if(!preg_match("/^1[34578]\d{9}$/", $post['phone'])){
            return $this->error(static::ERR_INVALID_MOBILE['code'], "手机号格式错误");
        }
        $userPhone = UserService::forgetPwd($post);
        return $this->success($userPhone);
    }

    /**
     * @api {post} user/index/valicode  Index Valicode
     * @apiVersion 1.0.0
     * @apiName Index Valicode
     * @apiGroup User
     *
     * @apiParam {String} phone 手机号
     * @apiParam {String} phonecode 验证码
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": {},
    "timestamp": 1525915355
    }
     *
     * @apiError CodeError The errorcode is error.
     *
     * @apiErrorExample Error-Response:
     *{
    "code": 0,
    "message": "验证码错误！",
    "data": {},
    "timestamp": 1525914678
    }
     */
    public function actionValicode() {
        $post = Yii::$app->getRequest()->post();
        $rules = [
            [['phone', 'phonecode'], 'required'],
            [['phone'], 'string'],
            [['phonecode'], 'string', 'length' => 6],
        ];
        $this->validate($post, $rules);
        $post['phone'] = trim($post['phone']);
        if(!preg_match("/^1[34578]\d{9}$/", $post['phone'])){
            return $this->error(static::ERR_INVALID_MOBILE['code'], "手机号格式错误");
        }
        UserService::valiPhoneCode($post['phone'], $post['phonecode']);
        return $this->success();
    }

    public function actionValitoken() {
        return $this->success();
    }











}