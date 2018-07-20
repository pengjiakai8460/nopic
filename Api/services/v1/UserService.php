<?php

namespace Api\services\v1;

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
use Yii;

class UserService extends BaseService
{
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

}
