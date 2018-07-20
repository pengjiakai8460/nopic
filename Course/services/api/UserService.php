<?php

namespace Course\services\api;

use common\models\orm\XmReportExam;
use common\models\orm\XmUserRate;
use common\base\BaseService;
use common\models\orm\XmCUserDetail;
use common\models\orm\XmReportExamQuestion;
use common\models\orm\XmCUserVip;
use common\models\orm\XmCAlipay;
use common\models\orm\XmCTag;
use common\models\orm\XmCQuestionTags;
use common\models\orm\XmCConfig;
use common\models\orm\XmReportUserData;
use common\models\orm\XmCFeedback;
use common\models\orm\XmUsers;
use common\models\orm\XmSendphonecode;
use common\models\orm\XmSchool;
use common\models\orm\XmArea;
use Course\services\api\WechatService;
use common\models\orm\XmVClasses;
use common\models\orm\XmVClassesUsers;
use Yii;


class UserService extends BaseService
{
    //小码世界教学服务公众号微信参数
    private static $appid = 'wx60ab09a315faea22';
    private static $appsecret = '57465a0c044eff06f4f73aab04ae3b5e';
    private static $grant_type = 'client_credential';
    private static $_models = array();
    //const SSO_TOKEN_URL = "http://sso.xiaomawang.com/sso_auth.php";
    const SSO_TOKEN_URL = "http://auth.xiaomawang.com/sso_auth.php";
    //const SSO_LOGIN_URL = "http://sso.xiaomawang.com/sso_login.php";
    const SSO_LOGIN_URL = "http://auth.xiaomawang.com/sso_login.php";
    //const SSO_LOGINOUT_URL = "http://sso.xiaomawang.com/sso_loginout.php";
    const SSO_LOGINOUT_URL = "http://auth.xiaomawang.com/sso_loginout.php";
    //const DEFAULT_IMG = "http://oss.xiaoma.wang/Public/Scratch/Scrachxin/image/xiaoma.png";
    const DEFAULT_IMG = "http://xmyj.oss-cn-shanghai.aliyuncs.com/Uploads/xmsj/front/img/b-icon.png";
    //const DEFAULT_BIG_IMG = "http://xmyj.oss-cn-shanghai.aliyuncs.com/Uploads/xmsj/front/img/s-icon.png";

    const UV_KEY = "uv_key_";

    public static $userInfo;

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

    //登录
    public static function login($token, $username, $pwd)
    {
        $tokens = self::getToken($token);
        $data['token'] = isset($tokens['result']['token_key']) ? $tokens['result']['token_key'] : '';
        setcookie('token_key', $data['token'], null, '/');
        if ($tokens) {
            if (isset($tokens['result']['info'])) {
                $info = json_decode($tokens['result']['info'], true);
                $data['uid'] = isset($info['id']) ? $info['id'] : '';
                $data['nickname'] = isset($info['nickname']) ? $info['nickname'] : '';
                $data['avatar_img'] = isset($info['avatar_img']) ? $info['avatar_img'] : env('DEFAULT_IMG');

            } else {
                $url = env('SSO_LOGIN_URL') . "?token=" . $data['token'] . "&phone=" . $username . "&password=" . $pwd;
                $res = file_get_contents($url);
                $ret = json_decode($res, true);
                //print_r($ret);exit;
                if ($ret['code'] && isset($ret['result']['info']) && !empty($ret['result']['info'])) {
                    $data['uid'] = $ret['result']['info']['id'];
                    $data['nickname'] = $ret['result']['info']['nickname'];
                    $data['avatar_img'] = isset($ret['result']['info']['avatar_img']) ? $ret['result']['info']['avatar_img'] : env('DEFAULT_IMG');
                } else {
                    return self::error($ret['status'], $ret['message']);
                }
            }
        }
        if (isset($data['uid']) && !empty($data['uid'])) {
            self::userCount($data['uid'], $data, true);
        }

        return array_merge($data, self::$userInfo ?? []);
    }

    //用户登录信息记录
    public static function userCount($uid, $data, $isLogin = false)
    {
        $day = date("Ymd");
        RedisService::hSet(self::UV_KEY . $day, $uid, 1);
        $userInfo = XmCUserDetail::findOne(["user_id" => $uid]);
        $time = time();
        $t1 = strtotime($day);
        if (empty($userInfo)) {
            if ($isLogin) {
                $userInfo = new XmCUserDetail();
                $userInfo->user_id = $uid;
                $userInfo->is_delete = 0;
                $userInfo->avator = $data['avatar_img'];
                $userInfo->account_balanc = 0;
                $userInfo->online = 1;
                $userInfo->last_login_time = $t1;
                $userInfo->login_day = 1;
                $userInfo->all_day = 1;
                $userInfo->status = 1;
                $userInfo->add_time = $time;
                $userInfo->update_time = $time;
                $userInfo->save();
            }
        } else {
            $u = $userInfo->toArray();
            if ($isLogin) {
                if ($u['last_login_time'] >= ($t1 - 86400) && date("Ymd", strtotime("-1 day")) == date("Ymd", $u['last_login_time'])) {
                    $userInfo->login_day = $u['login_day'] + 1;
                }
                if ($t1 > $u['last_login_time'] + 86400) {
                    $userInfo->login_day = 1;
                }
                if (isset($data['avatar_img']) && !empty($data['avatar_img'])) {
                    $userInfo->avator = $data['avatar_img'];
                }
                $userInfo->all_day = $u['all_day'] + 1;
                $userInfo->last_login_time = $time;
                $userInfo->update_time = $time;
                $userInfo->save();
            }

        }
        //判断当前用户是否是VIP用户
        $isVip = 0;
        $vip = XmCUserVip::find()->where(['user_id' => $userInfo->user_id, 'status' => 1])
            ->andWhere(['<=', 'begin_time', $time])
            ->andWhere(['>', 'end_time', $time])->asArray()->one();
        if (!empty($vip)) {
            $isVip = 1;

            //判断还剩余多少天
            $last_day = ceil(($vip['end_time'] - $time) / 86400);
        }

        self::$userInfo = [
            'uid' => $userInfo->user_id,
            'last_login_time' => date('Y-m-d H:i', $userInfo->last_login_time),
            'login_day' => $userInfo->login_day,
            'autograph' => $userInfo->autograph,
            'nickname' => $data['nickname'],
            'img' => $data['avatar_img'],
            'is_vip' => $isVip,
            'vip_day' => $last_day ?? 0,
        ];

        return true;

    }

    /**
     * 获取token
     * @param array $data
     * @return boolean
     */
    public static function getToken($token = '')
    {
        $url = env('SSO_TOKEN_URL') . "?token=" . $token;
        $res = file_get_contents($url);
        if (empty($res)) {
            return false;
        } else {
            $tokens = json_decode($res, true);
            return $tokens;
        }
    }

    //退出单点登录
    public static function loginOut($token = '')
    {
        $url = env('SSO_LOGINOUT_URL') . "?token=" . $token;
        file_get_contents($url);
        return [];
    }

    /**
     * 获取首页数据
     * @param $userInfo
     * @return mixed
     */
    public static function getHomePageData($userInfo)
    {
        //config
        $xmconfig = XmCConfig::find()->where(['in', 'key', ['MONTH_PRICE', 'YEAR_PRICE']])->andWhere(['status' => 1])->select('key,value')->asArray()->all();
        $xmconfig = isset($xmconfig) ? array_column($xmconfig, 'value', 'key') : [];
        $userInfo['price'] = $xmconfig;
        $userInfo[''] = '';

        //获取练习数量
        $qIdArr = [];
        $userInfo['q_count'] = 0;
        $reportIdArr = XmReportExam::find()
            ->where(['user_id' => $userInfo['uid']])
            ->andWhere(['is_accept' => 1])
            ->andWhere(['status' => 1])
            ->select('id')->asArray()->all();
        $reportIdArr = array_column($reportIdArr, 'id');

        if ($reportIdArr) {
            $reportData = XmReportExamQuestion::find()->where(['in', 'report_id', $reportIdArr])->andWhere(['status' => 1])->select('q_id,is_right')->asArray()->all();
            $qIdArr = array_column($reportData, 'q_id');
            $qIdIsRightArr = array_column($reportData, 'is_right', 'q_id');
            $userInfo['q_count'] = $reportData ? count($reportData) : 0;
        }

        //获取能力指数
        $userInfo['capa'] = [];//
        $capability = [];
        //获取顶级标签信息
        $tagTopData = XmCTag::find()->where(['pid' => 0])->andWhere(['status' => 1])->select('id,name')->asArray()->all();
        //初始化能力指数
        foreach ($tagTopData as $k => $v) {
            $capability[$v['id']]['name'] = $v['name'];
            $capability[$v['id']]['right_count'] = 0;
            $capability[$v['id']]['all_count'] = 0;
            $capability[$v['id']]['score'] = 0;
        }
        $isShow = true;
        if (isset($reportData) && $reportData) {
            $qIdTagIdArr = XmCQuestionTags::find()->where(['in', 'q_id', $qIdArr])->andWhere(['status' => 1])->select('q_id,tag_id')->asArray()->all();
            if ($qIdTagIdArr) {

                //获取当前问题对应标签
                $tagIdArr = array_column($qIdTagIdArr, 'tag_id', 'q_id');
                $tagData = XmCTag::find()->where(['in', 'id', $tagIdArr])->select('id,top,name')->asArray()->all();
                $tagIdTopArr = array_column($tagData, 'top', 'id');


                //[0=>['q_id'=>1,'is_right'=>1]]
                foreach ($reportData as $k => $v) {
                    if (isset($tagIdArr[$v['q_id']])) {
                        if (isset($capability[$tagIdTopArr[$tagIdArr[$v['q_id']]]])) {
                            $capability[$tagIdTopArr[$tagIdArr[$v['q_id']]]]['right_count'] += $qIdIsRightArr[$v['q_id']];
                            $capability[$tagIdTopArr[$tagIdArr[$v['q_id']]]]['all_count'] += 1;
                        } else {
                            $capability[$tagIdTopArr[$tagIdArr[$v['q_id']]]]['all_count'] = 1;
                            $capability[$tagIdTopArr[$tagIdArr[$v['q_id']]]]['right_count'] = (int)$qIdIsRightArr[$v['q_id']];
                        }
                    }
                }

                foreach ($capability as $k => $v) {
                    if ($v['all_count'] < 10) {
                        $capability[$k]['score'] = 0;
//                      $isShow = false;
                    } elseif ($v['all_count'] <= 50) {
                        $capability[$k]['score'] = floor($v['right_count'] / $v['all_count'] * 0.3 * 100);
                    } elseif ($v['all_count'] <= 100) {
                        $capability[$k]['score'] = floor($v['right_count'] / $v['all_count'] * 0.5 * 100);
                    } elseif ($v['all_count'] <= 300) {
                        $capability[$k]['score'] = floor($v['right_count'] / $v['all_count'] * 0.8 * 100);
                    } else {
                        $capability[$k]['score'] = floor($v['right_count'] / $v['all_count'] * 0.9 * 100);
                    }
                }
            }
        }
        //是否显示能力指数
        $userInfo['capa'] = $isShow ? $capability : [];

        //正确率
        $userRateData = XmUserRate::find()->where(['uid' => $userInfo['uid']])->asArray()->one();
        $userInfo['correct_rate'] = 0;
        if ($userRateData) {
            $userInfo['correct_rate'] = ($userRateData['type1_right'] + $userRateData['type2_right'] +
                    $userRateData['type3_right'] + $userRateData['type4_right']) / ($userRateData['type1_all'] + $userRateData['type2_all'] + $userRateData['type3_all'] + $userRateData['type4_all']);
            $userInfo['correct_rate'] = sprintf("%2d", $userInfo['correct_rate'] * 100);
        }
        //
        $userInfo['over_rate'] = 3 / 5 * log(40 * $userInfo['correct_rate']);
        $userInfo['over_rate'] = sprintf('%2d', $userInfo['over_rate']);
        return $userInfo;
    }

    /**
     * 获取历史正确率
     * @param $get
     * @return array
     */
    public static function getHistoryRate($get)
    {
        $uid = UserService::$userInfo['uid'];

        $historyRateM = XmReportUserData::find()->where(['user_id' => $uid])->andWhere(['status' => 1]);
        if (isset($get['start_date']) && $get['start_date']) {
            $historyRateM->andWhere(['>=', 'calcu_date', date('Ymd', $get['start_date'])]);
        }
        if (isset($get['end_date']) && $get['end_date']) {
            $historyRateM->andWhere(['<=', 'calcu_date', date('Ymd', $get['end_date'])]);
        }


        if (isset($get['search'])) {

            switch ($get['search']) {
                case 'week':
                    //$start = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
                    //$end = date("Ymd", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y")));
                    $start = date("Ymd", strtotime("-7 day"));
                    $end = date("Ymd",time());
                    break;
                case 'month':
                    //$start = date("Ymd", mktime(0, 0, 0, date("m"), 1, date("Y")));
                    //$end = date("Ymd", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
                    $start = date("Ymd", strtotime("-30 day"));
                    $end = date("Ymd",time());
                    break;
                case 'year':
                    $start = date('Y0101');
                    $end = date('Y1231');
                    break;
                default:
                    break;
            }
            if (isset($start)) {
                $historyRateM->andWhere(['>=', 'calcu_date', $start]);
            }
            if (isset($end)) {
                $historyRateM->andWhere(['<=', 'calcu_date', $end]);
            }

        }

        $rateData = $historyRateM->select('id,user_id,correct_rate,calcu_date')->orderBy('calcu_date')->asArray()->all();
        foreach ($rateData as $k => $v) {
            $rateData[$k]['calcu_time'] = strtotime($v['calcu_date']);
        }

        return count($rateData) >= 2 ? $rateData : [];
    }

    //获取SSO验证信息
    public static function auth($token)
    {
        $tokens = self::getToken($token);
        $data['token'] = isset($tokens['result']['token_key']) ? $tokens['result']['token_key'] : '';
        setcookie('token_key', $data['token'], null, '/');
        if ($tokens) {
            if (isset($tokens['result']['info'])) {
                $info = json_decode($tokens['result']['info'], true);
                $data['uid'] = isset($info['id']) ? $info['id'] : '';
                $data['nickname'] = isset($info['nickname']) ? $info['nickname'] : '';
                $data['img'] = isset($info['avatar_img']) ? $info['avatar_img'] : env('DEFAULT_IMG');
            }
        }
        return $data;
    }

    //支付回调结果
    public static function payResult($data)
    {
        $ti = time();
        //$token = $_COOKIE['token_key'] ?? '';
        ///$userInfo = self::auth($token);
        $alipay = XmCAlipay::find()->where(['out_trade_no' => $data['oid']])->asArray()->one();
        if (empty($alipay)) {
            return self::error(0, "该订单不存在!");
        }

        if ($alipay['status'] != 1) {
            return self::error(1, "该订单正在支付中,请稍后重试!");
        } else {
            if ($alipay['used_status']) {
                return self::error(2, "该订单已经使用过,不能重复使用!");
            } else {
                if ($alipay['unit_type'] == 1) {
                    $unit = ' month';
                } else {
                    $unit = ' year';
                }

                $userVip = XmCUserVip::findOne(['user_id' => $alipay['users_id']]);
                if (empty($userVip)) {
                    $end_time = strtotime("+{$alipay['num']} {$unit}", $ti);
                    $userVip = new XmCUserVip();
                    $userVip->user_id = $alipay['users_id'];
                    $userVip->level = 1;
                    $userVip->begin_time = $ti;
                    $userVip->end_time = $end_time;
                    $userVip->money = $alipay['money'] ?? 0;
                    $userVip->status = 1;
                    $userVip->add_time = $ti;
                    $userVip->update_time = $ti;
                    $r = $userVip->save();
                } else {
                    $userV = $userVip->toArray();
                    $end_time = strtotime("+{$alipay['num']} {$unit}", $userV['end_time']);
                    $userVip->end_time = $end_time;
                    $userVip->money += $alipay['money'];
                    $userVip->update_time += $ti;
                    $r = $userVip->save();
                }

                if ($r) {
                    $pay = XmCAlipay::findOne(['out_trade_no' => $data['oid'], 'status' => 1]);
                    $pay->used_status = 1;
                    $pay->update_time = $ti;
                    $r1 = $pay->save();
                    if (!$r1) {
                        $pay->save();
                    }
                }
                $return['end_time'] = $end_time;
                return $return;

            }

        }

    }

    //vip信息
    public static function vipDetails()
    {
        $uid = UserService::$userInfo['uid'];
        if (empty($uid)) {
            return false;
        }
        $vip = XmCUserVip::find()->select("begin_time, end_time")->where(['user_id' => $uid, 'status' => 1])->asArray()->one();
        if (empty($vip)) {
            $data['is_vip'] = 0;
            $data['end_time'] = 0;
            return $data;
        }
        $ti = time();
        if ($vip['begin_time'] <= $ti && $vip['end_time'] > $ti) {
            $data['is_vip'] = 1;
            $data['end_time'] = $vip['end_time'];
        } else {
            $data['is_vip'] = 0;
            $data['end_time'] = 0;
        }
        return $data;
    }

    //收集用户的意见反馈
    public static function feedback($data)
    {
        $uid = UserService::$userInfo['uid'];
        $ti = time();
        $feedback = new XmCFeedback();
        $feedback->uid = $uid;
        $feedback->title = $data['title'];
        $feedback->type = $data['type'];
        $feedback->content = $data['content'];
        $feedback->extra = $data['extra'];
        $feedback->status = 1;
        $feedback->add_time = $ti;
        $feedback->update_time = $ti;
        $rep = $feedback->save();
        if ($rep) {
            return ['ret' => 1];
        } else {
            return ['ret' => 0];
        }
    }

    //根据手机号判断用户是否存在
    public static function valiPhone($phone)
    {
        //$users = XmUsers::findOne(['or',['phone' => $phone], ['account' => $phone]]);
        $users = XmUsers::find()->select("id")->where(['or', ['phone' => $phone], ['account' => $phone]])->asArray()->one();
        if (!empty($users)) {
            //return self::error(0, "该手机号已存在！");
            return ['isExists' => 1];
        } else {
            return ['isExists' => 0];
        }
    }

    //根据注册或者找回密码返回不同逻辑1:找回密码  2:注册
    public static function valiPhoneByType($phone, $type = 2)
    {
        $ph = self::valiPhone($phone);
        if ($type == 1) {
            if ($ph['isExists']) {
                return true;
            } else {
                return self::error(0, "该手机号不存在，请重新输入！");
            }
        }

        if ($type == 2) {
            if (!$ph['isExists']) {
                return true;
            } else {
                return self::error(0, "该手机号已存在！");
            }
        }

    }

    //发送验证码
    public static function phoneCode($phone, $type)
    {
        $ret = self::valiPhone($phone);
        if ($type == 2) {
            if ($ret['isExists']) {
                return self::error(0, "该手机号已存在！");
            }
        } elseif ($type == 1) {
            if (!$ret['isExists']) {
                return self::error(0, "该手机号不存在，请重新输入！");
            }
        }
        //首先判断是测试环境还是正式环境 测试环境不发送验证码
        if (env('APP_ENV', 'dev') == 'dev') {
            $ret['result'] = 'ok';
            $ret['info'] = '发送成功';
            return $ret;
        }

        $url = env('PhoneCodeUrl', '');
        //初始化
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        $post_data = array(
            "phone" => $phone,
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($curl);
        curl_close($curl);
        $d = json_decode($data, true);
        if ($d['result'] == 'ok') {
            return $d;
        } else {
            return self::error(0, $d['info']);
        }
    }

    //验证验证码是否正确
    public static function valiPhoneCode($phone, $code)
    {
        if (empty($phone) || empty($code)) {
            return self::error(0, "手机号和验证码不能为空！");
        }
        if (env('APP_ENV', 'dev') == 'dev' && $code == 999999) {
            $ret['result'] = 'ok';
            $ret['info'] = '发送成功';
            return $ret;
        }
        $phoneCodeInfo = XmSendphonecode::find()->where('phone="' . $phone . '" and code="' . $code . '"')->orderBy('id desc')->asArray()->one();
        if (!empty($phoneCodeInfo)) {
            return true;
        } else {
            return self::error(0, "验证码错误！");
        }
    }

    //注册账号
    public static function register($data)
    {
        //验证手机号是否存在
        $ret = self::valiPhone($data['phone']);
        if ($ret['isExists']) {
            return self::error(101, "该手机号已存在！");
        }
        //验证验证码是否正确
        self::valiPhoneCode($data['phone'], $data['phonecode']);

        //插入用户数据
        $nickname = substr($data['phone'], 0, 3) . "****" . substr($data['phone'], 7, 4);
        $ti = time();
        $users = new XmUsers();
        $users->account = $data['phone'];
        $users->password = md5($data['pwd']);
        $users->name = $nickname;
        $users->nickname = $nickname;
        $users->phone = $data['phone'];
        $users->email = '';
        $users->sex = 0;
        $users->intention_level = 'E';
        $users->from_type = 1;
        $users->adder_id = 0;
        $users->add_time = $ti;
        $users->update_time = $ti;
        $users->status = 1;
        $users->os_from = 'NOIP';
        $users->reg_ip = Yii::$app->request->userIp;
        $users->xmschool_id = 0;
        $ret = $users->save();
        if ($ret) {
            $login = self::login('', $data['phone'], $data['pwd']);
            return $login;
        } else {
            $r = $users->save();
            if ($r) {
                $login = self::login('', $data['phone'], $data['pwd']);
                return $login;
            } else {
                return self::error(0, "保存失败，请稍后重试！");
            }
        }
    }

    //修改密码
    public static function forgetPwd($data)
    {
        if ($data['phone']) {
            $users = XmUsers::find()->select("id")->where(['or', ['phone' => $data['phone']], ['account' => $data['phone']]])->asArray()->one();
            if (empty($users)) {
                return self::error(0, "该手机号不存在！");
            }
        }

        if ($data['phone'] && $data['phonecode']) {
            self::valiPhoneCode($data['phone'], $data['phonecode']);
        }

        $ti = time();
        $pwd = md5($data['pwd1']);
        $r = XmUsers::updateAll(
            ['update_time' => $ti, 'password' => $pwd],
            "phone = :phone or account = :phone",
            [":phone" => $data['phone']]
        );

        if ($r) {
            return true;
        } else {
            return self::error(0, "密码修改失败，请稍后重试！");
        }

    }


    public static function getUserinfo($id)
    {
        $userinfo = array();
        $userinfo =  XmUsers::find()->select('id,nickname,sex,name,age,province_code,city_code,area_code,school_id,admission_year,avatar_img,autograph,phone,openid')->where(['id' => $id])->asArray()->one();
        $userinfo['phone'] = self::hidtel($userinfo['phone']);
        if(!empty($userinfo['province_code']))
        {
            $province = XmArea::find()->select('name')->where(['id' => $userinfo['province_code']])->asArray()->one();
            $userinfo['province'] = $province['name'];
        }
        else
        {
            $userinfo['province'] = '';
        }

        if(!empty($userinfo['city_code']))
        {
            $city = XmArea::find()->select('name')->where(['id' => $userinfo['city_code']])->asArray()->one();
            $userinfo['city'] = $city['name'];
        }
        else
        {
            $userinfo['city'] = '';
        }

        if(!empty($userinfo['area_code']))
        {
            $area = XmArea::find()->select('name')->where(['id' => $userinfo['area_code']])->asArray()->one();
            $userinfo['area'] = $area['name'];
        }
        else
        {
            $userinfo['area'] = '';
        }

        if(!empty($userinfo['avatar_img']))
        {
            $obj = json_decode($userinfo['avatar_img']);
            $userinfo['avatar_img'] = $obj;
        }
        else
        {
            $userinfo['avatar_img'] = env('DEFAULT_IMG');
        }

        if(!empty($userinfo['school_id']))
        {
            $school_name = XmSchool::find()->select('name as school_name')->where(['id' => $userinfo['school_id']])->asArray()->one();
            $userinfo['school_name'] = $school_name['school_name'];
        }
        else
        {
            $userinfo['school_name'] = '';
        }
        return $userinfo;
    }

    public static function getAreas($id)
    {
        $data = XmArea::find()->select('id,name')->where(['actid' => $id])->asArray()->all();
        return $data;
    }

    /**
     * 获取学校列表
     * @param $name string 学校名称
     */
    public static function getSchools($name)
    {
        $data = XmSchool::find()->select('id as school_id,name')->where(['like','name',$name])->limit(10)->asArray()->all();
        return $data;
    }

    /**
     * 保存用户信息
     * @param $id int 用户id
     * @param $nickname string 昵称
     * @param $sex int 性别 1男2女
     * @param $age int 时间戳,出生年月
     * @param $name string 真实姓名
     * @param $admission_year int 入学年份
     * @param $avatar_img string 个人头像
     * @param $province string 省级名称
     * @param $city string 市名称
     * @param $area string 区名称
     * @param $province_code int 省代码
     * @param $city_code int 市代码
     * @param $area_code int 区代码
     * @param $school_id int 学校ID
     * @param $admission_year int 入学年份
     * @param $autograph string 个人签名
     */

    public static function saveUserinfo($id,$data)
    {
        $model = XmUsers::findOne(['id' => intval($id)]);
        $model->nickname = trim($data['nickname']);
        if(empty($model->intention_level)) $model->intention_level = 'E';
        if(isset($data['sex']) && !empty($data['sex']))
        {
            $model->sex = $data['sex'];
            if(!preg_match('/^\d+$/', $data['sex'])) return $data = array('code' => 2,'msg' => 'sex格式错误');
        }


        if(isset($data['age']) && !empty($data['age']))
        {
            $model->age = $data['age'];
            if(!preg_match('/^\d{9,10}$/', $data['age'])) return $data = array('code' => 2,'msg' => 'age格式错误');
        }


        if(isset($data['name'])) $model->name = $data['name'];
        if(isset($data['avatar_img']) && !empty($data['avatar_img'])) $model->avatar_img = json_encode($data['avatar_img']);
        if(isset($data['province'])) $model->province = $data['province'];
        if(isset($data['city'])) $model->city = $data['city'];
        if(isset($data['area'])) $model->area = $data['area'];
        if(isset($data['province_code']) && !empty($data['province_code']))
        {
            $model->province_code = $data['province_code'];
            if(!preg_match('/^\d+$/', $data['province_code'])) return $data = array('code' => 2,'msg' => 'province_code格式错误');
        }


        if(isset($data['city_code']) && !empty($data['city_code']))
        {
            $model->city_code = $data['city_code'];
            if(!preg_match('/^\d+$/', $data['city_code'])) return $data = array('code' => 2,'msg' => 'city_code格式错误');
        }


//        if(isset($data['area_code']) && !empty($data['area_code']))
        if(isset($data['area_code']))
        {
            $model->area_code = $data['area_code'];
            if(!preg_match('/^\d+$/', $data['area_code'])) return $data = array('code' => 2,'msg' => 'area_code格式错误');
        }


        if(isset($data['school_id']) && !empty($data['school_id']))
        {
            $model->school_id = $data['school_id'];
            if(!preg_match('/^\d+$/', $data['school_id'])) return $data = array('code' => 2,'msg' => 'school_id格式错误');
        }


        if(isset($data['admission_year']) && !empty($data['admission_year'])){
            $model->admission_year = $data['admission_year'];
            if(!preg_match('/^\d{4}$/', $data['admission_year'])) return $data = array('code' => 2,'msg' => 'admission_year格式为2018类似的四位数字');
        }

        if(isset($data['autograph'])) $model->autograph = $data['autograph'];
        return $model->save();
    }

    /**
     * 检测手机号是否存在
     * @param id int 用户id
     * @param phone int 手机号
     */
    public static function checkMobile($id,$phone)
    {
        $model = XmUsers::find()->select('phone')->where(['id' => intval($id),'phone' => $phone])->asArray()->one();
        if(!empty($model))
        {
            $model['phone'] = self::hidtel($model['phone']);
        }
        return $model;
    }


    public static function hidtel($phone){
        $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i',$phone); //固定电话
        if($IsWhat == 1)
        {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i','$1****$2',$phone);
        }
        else
        {
            return  preg_replace('/(1[3578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
        }
    }

    public static function replacePhone($id,$phone,$code)
    {
        $res = self::valiPhoneCode($phone, $code);
        if($res)
        {
            $model = XmUsers::findOne(['id' => $id]);
            if(empty($model->intention_level)) $model->intention_level = 'E';
            $model->phone = $phone;
            return $model->save();
        }
        else
        {
            return '手机号和验证码不匹配！';
        }

    }

    public static function forgetPassword($id,$data)
    {
        $model = XmUsers::findOne(['id' => $id]);
        if($model->password !== md5($data['old_password'])) return $data = array('code' => 1,'msg' => '旧密码错误');
        $model->password = md5($data['new_password']);
        if($model->save())
        {
            $data = array('code' => 2);
        }
        else
        {
            $data = array('code' => 1,'msg' => '修改失败');
        }
        return $data;
    }

    //微信扫码登录
    public static function wechatLogin($unionid)
    {
        $tokens = self::getToken();
        $data['token'] = isset($tokens['result']['token_key']) ? $tokens['result']['token_key'] : '';
        setcookie('token_key', $data['token'], null, '/');
        if ($tokens) {
            if (isset($tokens['result']['info'])) {
                $info = json_decode($tokens['result']['info'], true);
                $data['uid'] = isset($info['id']) ? $info['id'] : '';
                $data['nickname'] = isset($info['nickname']) ? $info['nickname'] : '';
                $data['avatar_img'] = isset($info['avatar_img']) ? $info['avatar_img'] : env('DEFAULT_IMG');
            } else {
                $url = env('SSO_LOGIN_URL') . "?token=" . $data['token'] . '&type=2' . '&unionid='.$unionid;
                $res = file_get_contents($url);
                $ret = json_decode($res, true);
                //print_r($ret);exit;
                if ($ret['code'] && isset($ret['result']['info']) && !empty($ret['result']['info'])) {
                    $data['uid'] = $ret['result']['info']['id'];
                    $data['nickname'] = $ret['result']['info']['nickname'];
                    $data['avatar_img'] = isset($ret['result']['info']['avatar_img']) ? $ret['result']['info']['avatar_img'] : env('DEFAULT_IMG');
                } else {
                    return self::error($ret['status'], $ret['message']);
                }
            }
            if (empty($data['uid'])) {
                $users = XmUsers::find()->where(['id'=>$data['uid']])->asArray()->one();
                $data['phone'] = $users['phone'];
            }
        }
        if (isset($data['uid']) && !empty($data['uid'])) {
            self::userCount($data['uid'], $data, true);
        }

        return array_merge($data, self::$userInfo ?? []);
    }

    /**
     * 获取用户绑定状态
     * @param id int 用户id
     * @return data array
     */
    public static function getUserBindInfo($id)
    {
        $userinfo = XmUsers::find()->select('phone,qq,openid')->where(['id' => $id])->asArray()->one();
        $data = $userinfo;
        if(!empty($userinfo['phone'])) $data['phone'] = self::hidtel($userinfo['phone']);
        $data['appid'] = self::$appid;
        $data['appsecret'] = self::$appsecret;

        if(!empty($userinfo['openid']))
        {
            //获取用户微信昵称
            //https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
            $openid = $userinfo['openid'];
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info';
            //RedisService::set('access_token', $ret['access_token'], 6800);
            /*$token = RedisService::get('access_token');
            if($token == '')
            {
                $token = WechatService::getToken();
            }*/
            $token = WechatService::getToken();
            $params = array('access_token' => $token,'openid' => $openid,'lang' => 'zh_CN');
            $json = WechatService::httpGet($url,$params);
            $arr = json_decode($json,true);
            $data['nickname'] = $arr['nickname'];
            //var_dump(json_decode($json,true));exit();
        }
        else
        {
            $data['nickname'] = '';
        }
        return $data;
    }

    /**
     * 查看用户是否绑定微信成功
     * @param id int 用户ID
     */
    public static function checkUserBindWechat($id)
    {
        $userinfo = XmUsers::find()->select('openid')->where(['id' => $id])->asArray()->one();
        if(!empty($userinfo['openid']))
        {
            $user = WechatService::getWechatUserinfo($userinfo['openid']);
            $userinfo['nickname'] = $user['nickname'];
            return $userinfo;
        }
        else
        {
            return '';
        }
        
    }

    //班级选择列表
    public static function chooseClass()
    {
        return XmVClasses::find()->select('id as class_id,name')->asArray()->all();
    }

    /**
     * 后台用户管理列表
     * @param id int 用户ID
     * @param phone string || number 用户手机号码
     * @param class_id int 班级ID
     * @return array
     */
    public static function getUserLists($page,$request)
    {   
        $query = XmUsers::find()->select('id');

        if(isset($request['id']) && !empty($request['id']))
        {
            $query->andWhere(['like','id',intval($request['id'])]);
        }
        if(isset($request['phone']) && !empty($request['phone']))
        {
            $query->andWhere(['like','phone',trim($request['phone'])]);
        }
        
        if(!empty($request['class_id']) && isset($request['class_id']))
        {
            $users = XmVClassesUsers::find()->select('usersId')->where(['classId' => $request['class_id']])->asArray()->all();
            $id = array();
            foreach ($users as $k => $v) {
                $id[] = $v['usersId'];
                unset($v);
            }
            $query->andWhere(['in','id',$id]);
        }
            
        $row = intval($request['limit']);//每页显示数目
        $pageSize = ($page - 1) * $row;
        $total = $query->count(); //总记录数
        $totalPage = ceil($total / $row);//总页数

        $data = array();
        $data['totalPage'] = $totalPage;
        $data['page'] = $page;//当前页码
        $data['total'] = intval($total);
        $data['pageSize'] = $row;

        $query_res = XmUsers::find()->select('id,nickname,phone,age,openid,teacher_lock,remark,province_code,city_code,area_code');
        if(isset($request['id']) && !empty($request['id'])){
            $query_res->andWhere(['like','id',intval($request['id'])]);
        }
        if(isset($request['phone']) && !empty($request['phone'])){
            $query_res->andWhere(['like','phone',trim($request['phone'])]);
        }
        if(!empty($request['class_id']) && isset($request['class_id']))
        {
            $users = XmVClassesUsers::find()->select('usersId')->where(['classId' => $request['class_id']])->asArray()->all();
           
            $id = array();
            foreach ($users as $k => $v) {
                $id[] = $v['usersId'];
                unset($v);
            }
            $query_res->andWhere(['in','id',$id]);
           
        }
        $data['userlists'] = $query_res->orderBy('id desc')->limit($row)->offset($pageSize)->asArray()->all();
        foreach ($data['userlists'] as $k => $v) 
        {
            $area = '';
            if(!empty($v['province_code']))
            {
                $res = XmArea::find()->select('name')->where(['id' => $v['province_code']])->asArray()->one();
                $area .= $res['name'];
            }
            if(!empty($v['city_code']))
            {
                $res = XmArea::find()->select('name')->where(['id' => $v['city_code']])->asArray()->one();
                $area .= $res['name'];
            }
            if(!empty($v['area_code']))
            {
                $res = XmArea::find()->select('name')->where(['id' => $v['area_code']])->asArray()->one();
                $area .= $res['name'];
            }
            if(!empty($v['age']))
            {
                $year = date('Y-m-d',$v['age']);
                $v['age'] = self::calcAge($year);
            }
            
            $v['area'] = $area;
            $data['userlists'][$k] = $v;
            unset($v);
        }
        return $data;
    }

    /**
     * 添加备注
     *
     * @param id int
     * @param remark string
     * @return bool || int
     */
    public static function remarks($data)
    {
        $res = XmUsers::updateAll(['remark' => trim($data['remark'])], ['id' => $data['id']]);
        return $res;
    }

    /**
     * 切换老师微信绑定状态
     *
     * @param id int 用户id
     * @param teacher_lock int 1未绑定，2绑定
     * @return bool || int
     */
    public static function bindTeacher($data)
    {
        $time = time();
        $res = XmUsers::updateAll(['teacher_lock' => intval($data['teacher_lock']),'update_time' => $time], ['id' => $data['id']]);
        return $res;
    }

    /**
     * @uses 根据生日计算年龄，年龄的格式是：2018-07-05
     * @param string $birthday
     * @return string|number
     */
    public static function calcAge($birthday) 
    {
        $age = 0;
        if (!empty($birthday)) 
        {
            $year = date('Y',strtotime($birthday));
            $month = date('m',strtotime($birthday));
            $day = date('d',strtotime($birthday));
            
            $now_year = date('Y');
            $now_month = date('m');
            $now_day = date('d');
    
            if ($now_year > $year) 
            {
                $age = $now_year - $year - 1;
                if ($now_month > $month) 
                {
                    $age ++;
                } 
                else if ($now_month == $month) 
                {
                    if ($now_day >= $day) 
                    {
                        $age ++;
                    }
                }
            }
        }
        return $age;
    }
}