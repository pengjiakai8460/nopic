<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/12
 * Time: 16:41
 */

namespace Api\services\v1;


use common\base\BaseService;
use common\models\orm\Game;
use common\models\orm\GameRankEx;
use common\models\orm\GameZone;
use common\models\orm\Member;
use common\models\orm\MemberPayRecode;
use common\models\orm\MemberProfile;
use common\models\orm\OrderAppealRecord;
use common\models\orm\OrderComment;
use common\models\orm\ReleaseOrder;
use common\models\orm\MemberSkill;
use common\models\orm\MemberPcOrder;
use common\models\utils\ArrayUtil;
use Yii;
use common\models\orm\MemberWealth;
use common\models\orm\Buyorder;
use yii\base\Object;
use yii\helpers\ArrayHelper;


class OrderService extends BaseService
{
    public static $errors = [
        60000 => '系统错误,请求失败！',
        60001 => '请求参数不全!',
        60002 => '该订单已被抢!',
        60003 => '该游戏ID不存在!',
        60004 => '该区服ID不存在!',
        60005 => '该段位ID不存在!',
        60006 => '请求的单价错误!',
        60007 => '请支付渠道不存在!',
        60008 => '钱包支付余额不足!',
        60009 => '记录操作失败!',
        60010 => '订单签名验证失败',
        60011 => '用户支付金额与订单金额验证失败',
        60012 => '订单修改不成功',
        60013 => '充值日志操作失败',
        60014 => '资金操作失败',
        60015 => '该时间段内没有合适的技能可以接单!',
        60016 => '该订单不存在!',
        60017 => '该鱼俠还未通过技能认证!',
        60018 => '该订单只能用户和鱼俠才能查看!',
        60019 => '资金处理完成',
        60020 => '一个同时在进行的订单数不能超过4个!',
        60021 => '该鱼侠关闭了胜率保证，无法接此订单',
        60022 => '该鱼侠开启了胜率保证，无法接此订单',

    ];

    CONST LOGO = 'http://flashfish.oss-cn-hangzhou.aliyuncs.com/CDN/image/logo.png';

    const ORDER_VALID_TTL = 600;

    public static $male = 1;
    public static $female = 2;
    public static $business_type = 5;
    public static $leveling_type = 4;

    public static $sys_uid = 0;//系统UID

    public static $dl_order = 1;//上分订单
    public static $send_order = 2;//已发出的订单
    public static $receiver_order = 3; // 已接受的订单

    public static $channels = ['ALI_APP', 'WX_APP', 'WALLET_APP'];
    public static $ali_channel = 'ALI_APP';
    public static $wx_channel = 'WX_APP';
    public static $wallet_channel = 'WALLET_APP';

    public static $order_begin = 200;
    public static $order_appeal = 322;
    public static $appealed_status = 736;
    public static $after_comment = 800;

    public static $zero = 0;

    public static $order_type_all = 1;
    public static $order_type_person = 2;

    public static $nothing = '无';

    public static $wallet_desc = "余额支付订单";
    public static $pay_method = "余额";
    public static $sdy_type = 1;
    public static $buyorder_desc = '闪电鱼充值';
    public static $buyorder_from = 'new_sdy';
    public static $log_desc = '用户发单';

    public static $game_royal = "王者荣耀";

    public static $zone_wx = ['苹果微信', '安卓微信'];
    public static $zone_qq = ['苹果QQ', '安卓QQ'];

    public static $order_types = ['personal', 'all'];

    public static $o_type = ['personal_desc' => '私人单', 'all_desc' => '匹配单', 'personal' => [], 'all' => []];

    public static $limit = 10;

    public static $user_type_self = 1;  //用户
    public static $user_type_pw = 2;   //鱼俠

    public static $no_victpromise = 0;  //无胜率保证
    public static $victpromise = 1;  //有胜率保证

    public static $sub_status = [
        1 => '剩余匹配时间',
        2 => '等待接单',
        3 => '等待鱼俠接单',
        4 => '对方上传申诉资料',
        5 => '我方上传申诉资料',
        6 => '等待客服仲裁',
        7 => '客服仲裁结果',
        8 => '自动确认剩余时间',
    ];


    public static $wait_accept_key = 'wait_accept_key';     //等待鱼俠接单
    public static $begin_service_key = 'begin_service_key';  //等待鱼俠开始服务
    public static $complete_service_key = 'complete_service_key'; //等待游侠完成服务
    public static $wait_confirm_key = 'complete_service_key'; //等待用户确认

    public static $waiting_operation = 'waiting_operation'; //等待操作

    public static $appeal_reasopn = '申请撤销';

    public static $appeal_status = [331, 321, 431, 421, 531, 521, 621, 631,
                                    732, 733, 734, 735,
                                    742, 743, 744, 745,
                                    752, 753, 754, 755,
                                    762, 763, 764, 765];
    public static $appealing_status_all = [331, 321, 431, 421, 531, 521, 621, 631];
    public static $appealed_status_all = [  732, 733, 734, 735,
                                        742, 743, 744, 745,
                                        752, 753, 754, 755,
                                        762, 763, 764, 765];


    public static $close_sub_status = [
        721 => '超时关闭',
        722 => '已取消',
        723 => '已拒绝',
        724 => '已关闭', //强制关闭
    ];

    public static $user_has_appeal = 1; //有申诉过


    /**
     * @param $data
     * @return array
     */
    public static function createOrderSn()
    {
        $str = 'PW' . Yii::$app->user->id . time() . rand(1000, 9999);
        $str = substr($str, 0, 26);
        return $str;
    }

    //创建订单
    public static function createOrder($uid, $data) {
        //判断该用户是否以下四单
        $count = ReleaseOrder::find()->where(['uid' => $uid, 'business_type' => self::$business_type])->andWhere(['>=', 'status', 200])->andWhere(['<', 'status', 700])->asArray()->count();
        if ($count > 4 && (!isset($data['beaterId']) || !$data['beaterId'])) {
            return self::error(60020);
        }

        if (isset($data['gameId']) && $data['gameId']) {
            $game = GameService::getGameByWhere(['id' => $data['gameId']]);
            if (empty($game)) {
                return self::error(60003);
            }
        }

        if (isset($data['zoneId']) && $data['zoneId']) {
            $zone = GameService::getZoneByWhere(['id' => $data['zoneId']]);
            if (empty($zone)) {
                return self::error(60004);
            }
        }

        if (isset($data['rankId']) && $data['rankId']) {
            $rank = GameService::getRankByWhere(['id' => $data['rankId']]);
            if (empty($rank)) {
                return self::error(60005);
            }

        }
        if (isset($data['sex']) && !empty($data['sex'])) {
            if ($data['sex'] == self::$male) {
                $data['unitprice'] = $rank[0]['male_price'];
//                if ($data['unitprice'] != $rank[0]['male_price']) {
//                    return self::error(60006);
//                }

            } else {
                $data['unitprice'] = $rank[0]['female_price'];
//                if ($data['unitprice'] != $rank[0]['female_price']) {
//                    return self::error(60006);
//                }
            }

            //判断是否是胜率保证的订单
            if (isset($data['victpromise']) && $data['victpromise'] == self::$victpromise) {
                //如果是私单
                if (isset($data['beaterId']) && $data['beaterId']) {
                    $vicePromise = UserSkillService::userOpenVictoriousPromiseForSkill($data['beaterId'], $data['gameId']);
                    if (!$vicePromise) {
                        return self::error(60021);
                    }

                } else {

                    $data['unitprice'] = $rank[0]['victorious_promise_price'];
                }

            } elseif (isset($data['victpromise']) && $data['victpromise'] == self::$no_victpromise) {
                //如果是匹配单
                if (isset($data['beaterId']) && $data['beaterId']) {
                    $vicePromise = UserSkillService::userOpenVictoriousPromiseForSkill($data['beaterId'], $data['gameId']);
                    if ($vicePromise) {
                        return self::error(60022);
                    }

                }

            }

        } else {
            $data['sex'] = 0;
        }

        if ($data['channel'] && !in_array($data['channel'], self::$channels)) {
            return self::error(60007);
        }

        if (isset($data['beaterId']) && $data['beaterId']) {
            $uskill = UserSkillService::getUserSkills($data['beaterId'], $data['gameId']);
            if (!$uskill) {
                return self::error(60017);
            }
            $data['unitprice'] = $uskill['price'] ?? 0;
        } else {
            $ra = GameService::getRankByWhere(['id' => $data['rankId']]);
            if (!$ra) {
                return self::error(60005);
            }
        }

        $price = $data['unitprice'] * $data['num'];
        if($data['channel'] == self::$wallet_channel) {
            $memberWealth = MemberWealth::find()->where(['uid' => $uid])->asArray()->one();
            if (!empty($memberWealth)) {
                if ($price > $memberWealth['money']) {
                    return self::error(60008);
                }
            }
            $sta = self::$order_begin;
        } else {
            $sta = self::$zero;
        }
        $fromMemberWealth = MemberWealth::getBalance($uid);
        $fromFreezingMoney = MemberPcOrder::getFreezingMoney($uid);

        $trans = Yii::$app->getDb()->beginTransaction();
        try {

            //插入订单数据
            $ti = time();
            $order_sn = self::createOrderSn();
            $realseOrder = new ReleaseOrder();

            //如果是余额支付直接冻结用户资金
            if($data['channel'] == self::$wallet_channel) {
                if (!CapitalService::model()->billOrder($uid, $order_sn, $price,
                    $fromMemberWealth, $fromFreezingMoney)
                ) {
                    return self::error(60019);
                } else {
                    self::getRedis()->zAdd(self::$wait_accept_key, time() + 600, $order_sn);
                    if (isset($data['beaterId']) && $data['beaterId']) {
                        $content = [
                            'subtitle' => '闪电鱼订单',
                            'body' => '您有私人单了',
                            'type' => 3
                        ];
                        MessageService::sendMessage([(string)$data['beaterId']], $content);
                    }
                }
            }

            //判断是私人单还是匹配单
            $b_type = self::$order_type_all;
            if (isset($data['beaterId']) && $data['beaterId']) {
                $data['zoneId'] = 0;
                $data['rankId'] = 0;
                $realseOrder->private_beater = $data['beaterId'];
                $b_type = self::$order_type_person;
            }
            $realseOrder->order_id = $order_sn;
            $realseOrder->uid = $uid;
            $realseOrder->buyer_id = $uid;
            $realseOrder->status = $sta;
            $realseOrder->price = $price;
            $realseOrder->really_price = $price;
            $realseOrder->security_deposit = self::$zero;
            $realseOrder->efficiency_deposit = self::$zero;
            $realseOrder->order_type = $b_type;
            $realseOrder->business_type = self::$business_type;
            $realseOrder->leveling_type = self::$leveling_type;
            $realseOrder->requirement_time = $data['num'];
            $realseOrder->create_time = $ti;
            $realseOrder->update_time = $ti;
            $realseOrder->game = $game[0]['mid'];
            $realseOrder->game_zone = $data['zoneId'] ?? 0;
            $realseOrder->game_rank = $data['rankId'] ?? 0;
            $realseOrder->game_sex = $data['sex'] ?? 0;
            $realseOrder->game_account = self::$nothing;
            $realseOrder->game_password = self::$nothing;
            $realseOrder->title = self::$nothing;
            $realseOrder->content = $data['content'] ?? '';
            $realseOrder->automatic_shelf = self::$zero;
            $realseOrder->is_delete = self::$zero;
            $realseOrder->double_row_type = self::$zero;
            $realseOrder->victorious_promise = $data['victpromise'];
            $rOrder = $realseOrder->save();

            //余额支付
            /**
            $mlog = 1;
            if ($data['channel'] == self::$wallet_channel) {
                $money_log = new MoneyLog();
                $money_log->order_id = $order_sn;
                $money_log->uid = $uid;
                $money_log->money = '-' . $price;
                $money_log->sum_wealth = $memberWealth['money'];
                $money_log->title = self::$wallet_desc;
                $money_log->pay_method = self::$pay_method;
                $money_log->createtime = $ti;
                $money_log->type = self::$sdy_type;
                $mlog = $money_log->save();
            }
             * **/

            //支付宝或者微信支付
            $bOrder = 1;
            $pRecord = 1;
            if ($data['channel'] == self::$ali_channel || $data['channel'] == self::$wx_channel) {
                $buyOrder = new Buyorder();
                $buyOrder->order_id = $order_sn;
                $buyOrder->goods_id = $order_sn;
                $buyOrder->ordername = self::$buyorder_desc;
                $buyOrder->money = $price * 100;
                $buyOrder->uid = (string)$uid;
                $buyOrder->status = self::$zero;
                $buyOrder->createtime = $ti;
                $buyOrder->paytime = self::$zero;
                $buyOrder->ispay = self::$zero;
                $buyOrder->from = $data['from'];
                $buyOrder->businesstype = self::$buyorder_desc;
                $bOrder = $buyOrder->save();

                //往用户充值记录表插入记录
                $payRecord = new MemberPayRecode();
                $payRecord->uid = $uid;
                $payRecord->oid = $order_sn;
                $payRecord->state = self::$zero;
                $payRecord->money = $price;
                $payRecord->created_time = $ti;
                $payRecord->completed_time = self::$zero;
                $payRecord->pay_time = self::$zero;
                $payRecord->pay_method = $data['channel'];
                $payRecord->businesstype = self::$buyorder_desc;
                $payRecord->oldbalacnce = 0;
                $payRecord->newbalacnce = 0;
                $payRecord->channel = $data['from'];
                $pRecord = $payRecord->save();
            } else {
                MemberPcOrder::findOne(['uid' => intval($uid)])->updateCounters(['release_count' => intval(1)]);

            }

            $StatusRecord = self::insertMongoOrderStatusRecord($uid, "", $order_sn, self::$order_begin, self::$log_desc, '', 0);
            if (!$StatusRecord) {
                return self::error(60009);
            }

            //获取order_id添加备注信息
            $create_time = $ti;
            $dataSec = [];
            $dataSec['order_id'] = $order_sn;
            $dataSec['create_time'] = $create_time;
            $dataSec['is_delete'] = 0;
            $dataSec['uid'] = $uid;
            $dataSec['order_price'] = !empty($post['p_price']) ? $post['p_price'] : '';
            $dataSec['comment_one'] = !empty($post['p1']) ? $post['p1'] : '';
            $dataSec['comment_two'] = !empty($post['p_note']) ? $post['p_note'] : '';
            MongoService::insert('pc_comment', $dataSec);

            if (!$rOrder || !$bOrder || !$pRecord) {
                self::error(60009);
            }

            $trans->commit();

        } catch (\Exception $e) {
            $trans->rollBack();
            self::error($e->getCode(), $e->getMessage());
        }

        $ret = [];
        $ret["order_id"] = $order_sn;
        $ret["channel"] = $data['channel'];
        $ret[ "amount"] = $price * 100;
        $ret["subject"] = self::$buyorder_desc;
        $ret["body"] = self::$buyorder_desc;
        $ret["notify_url"] = "";

        return $ret;


    }

    /**
     * 订单状态记录 入表
     * @param $uid 发起人
     * @param $touid
     * @param $orderId 订单号
     * @param $status  订单状态
     * @param $before_status  订单状态
     * @param $reason  原因
     * @param $money   退还给买家的钱
     */
    public static function insertMongoOrderStatusRecord($uid, $touid, $orderId, $status, $reason, $money, $beforeStatus, $images = '')
    {
        $data = array();
        $data['uid'] = intval($uid);
        $data['touid'] = intval($touid);
        $data['order_id'] = $orderId;
        $data['reason'] = addslashes($reason);
        $data['before_status'] = $beforeStatus;
        $data['status'] = $status;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['images'] = $images;
        $data['return_money'] = $money ? floatval($money) : null;
        $data['browser'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data['host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $data['route'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $data['ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $data['create_time_zh'] = date('Y-m-d h:i:s');
        $superId = MongoService::selectAll('order_status_record', ['order_id' => $orderId], 'create_time desc', '', '', 1);
        $data['super_id'] = $superId ? reset($superId)['_id'] : null;
        if (MongoService::insert('order_status_record', $data)) {
            return true;
        } else {
            return false;
        }
    }


    //支付回调
    public static function beeCloudCallback($data) {
        $ti = time();
        $appId = Yii::$app->params['payConfig']['beecloud']['appID'];
        //$appSecret = Yii::$app->params['payConfig']['beecloud']['appSecret'];
        $masterSecret = Yii::$app->params['payConfig']['beecloud']['masterSecret'];
        $data->optional = (array)$data->optional;
        $data->message_detail = (object)$data->message_detail;
        MongoService::insert('paylogdata', $data);
        $myorder = Buyorder::findOne(['order_id' => $data->transaction_id]);
        $order = ReleaseOrder::find()->where(['order_id'=> $data->transaction_id, 'status' => self::$zero])->asArray()->one();
        if (empty($order)) {
            return '匹配订单不存在!';
        }
        if (empty($myorder) || !isset($myorder->status)) {  // 查询我方数据库中有无此订单
            PayService::recordOrder($data, '订单不存在', null, 1);                  // 记录错误订单信息
            return '订单不存在!';
        } else if ($myorder->status == 4) {
            PayService::recordOrder($data, '订单已支付', null, 1);                  // 记录错误订单信息
            echo 'success';
            return;
        }
        if (md5($appId . $data->transaction_id . $data->transaction_type . $data->channel_type . $data->transaction_fee . $masterSecret) != $data->signature) {      # 验证签名
            PayService::recordOrder($data, '订单签名验证失败', $myorder, 1);            # 记录订单信息
            return '签名验证失败';
        }


        //验证订单金额与回调金额是否匹配
        if (intval($myorder['money']) != intval($data->transaction_fee)) { # 判断支付金额与订单金额是否相等
            PayService::recordOrder($data, '用户支付金额与订单金额验证失败', $myorder, 1, "");  # 记录订单信息
            echo '金额错误';
            return;
        }

        $fromMemberWealth = MemberWealth::getBalance($order['uid']);
        $fromFreezingMoney = MemberPcOrder::getFreezingMoney($order['uid']);

        if ($data->trade_success) {     //trade_success 为true支付成功，同一订单 false不代表失败，还可能发送为true的消息
            if ($order['order_type'] == 2) {
                if (isset($order['beaterId']) && $order['beaterId']) {
                    $content = [
                        'subtitle' => '闪电鱼订单',
                        'body' => '您有私人单了',
                        'type' => 3
                    ];
                    MessageService::sendMessage([(string)$order['beaterId']], $content);
                }
            }
            if ($data->transaction_type == "PAY") {      # 支付
                $buyorder = $myorder;

                $oldbalacnce = MemberWealth::getBalance($buyorder['uid']);
                if (!empty($data->transaction_fee)) {
                    $buyorder->money = bcdiv($data->transaction_fee, 100, 2);       # 充值订单以成功支付金额为准
                }
                $buyorder->status = 4;
                $buyorder->ispay = 1;        # 订单状态 已付款
                $buyorder->paytime = time();
                $error = '';
                $transaction = Yii::$app->db->beginTransaction();
                $ma = new MemberWealth();
                $mBanlance1 = $ma->getBalance($buyorder->uid);
                $result = false;
                try {

                    if (!$buyorder->save()) {
                        $error = '订单修改不成功';
                        throw new \Exception('live_order表status数据更新不成功');
                    }

                    if (!PayService::recordSuccessOrder($data, $buyorder, $oldbalacnce)) {
                        throw new \Exception('充值日志操作失败');
                    }
                    if (!CapitalaaService::charge($buyorder->uid, (string)$buyorder->id, $buyorder->money, $mBanlance1)) {
                        $error = '资金操作失败';
                        throw new \Exception('资金操作失败');
                    };

                    $result = true;

                    //把已发单数量加1
                    MemberPcOrder::findOne(['uid' => intval($buyorder['uid'])])->updateCounters(['release_count' => intval(1)]);


                    //把release_order 状态置为发布中
                    $realeseOrder = ReleaseOrder::findOne(['order_id' => $data->transaction_id]);
                    $realeseOrder->status = self::$order_begin;
                    $realeseOrder->update_time = $ti;
                    $r = $realeseOrder->save();
                    //提交事务
                    $transaction->commit();

                } catch (\Exception $e) {
                    $transaction->rollback();
                    MongoService::insert('debug_live_order_e', [
                        'error' => $error,
                        'errorsCode' => $e->getCode(),
                        'errorsFile' => $e->getFile(),
                        'errorsLine' => $e->getLine(),
                        'errorsMessage' => $e->getMessage(),
                        'errorsTraceAsString' => $e->getTraceAsString(),
                        'createtime' => time(),
                        'order_id' => $buyorder->order_id,
                    ]);

                }

                //如果是余额支付直接冻结用户资金
                if (!CapitalService::model()->billOrder($order['uid'], $order['order_id'], $order['price'],
                    $fromMemberWealth, $fromFreezingMoney)) {
                    return self::error(60019);
                }

                //将该订单写入redis中  等待延时处理
                self::getRedis()->zAdd(self::$wait_accept_key, $realeseOrder->create_time + 600, $data->transaction_id);
                if ($result) {
                    exit("charge succeeded");
                } else {
                    exit("charge fail");
                }
            }
        } else {
            PayService::model()->recordOrder($data, '支付失败', $myorder, 1);
            echo 'success';
            return;
        }
    }


    //获取订单列表页
    public static function getOrderList($uid) {
        //先判断该用户是否还可以接匹配单
        $skillIds = UserSkillService::canUserReceiveOrderSkillIds($uid);

        // 接单大厅只屏蔽了匹配单,所以这里要对订单的匹配游戏规则进行区分
//        if (empty($skillIds)) {
//            return self::error(60015);
//        }

        //获取人物技能相关信息
        $data = MemberSkill::find()->select(['member_skill.id','member_skill.game_id','member_skill.zone_id', 'member_skill.rank_id',
            'member_skill.extension', 'member_skill.receive_msg', 'mg.order', 'mg.unit', 'mg.rank', 'mg.sub_title', 'g.mid', 'g.game'])
            ->leftJoin('game_rank_ex mg', 'member_skill.rank_id = mg.id')
            ->leftJoin('game g', 'member_skill.game_id = g.id')
//            ->where(['in', 'member_skill.id', $skillIds])
            ->where(['member_skill.uid' => $uid, 'member_skill.status' => MemberSkill::SKILL_STATUS_CERTIFIED, 'member_skill.deleted_at' => 0])
            ->asArray()->all();

        $last10Min = time() - self::ORDER_VALID_TTL;
        $game_mids = ArrayHelper::getColumn($data, 'mid');
        //$game_ids = ArrayHelper::getColumn($data, 'game_id');
        $temp = ArrayHelper::map($data, 'id', 'mid', 'id');
        $game_ids = ArrayHelper::map($data, 'mid', 'game_id');
        $match_game_mids = [];
        foreach ($temp as $sub_match) {
            foreach ($skillIds as $id) {
                if (isset($sub_match[$id])) {
                    $match_game_mids[] = $sub_match[$id];
                }
            }
        }

        $games = ArrayHelper::index($data, null, 'mid');
        $ranks = [];

        foreach ($games as $game) {
            $sub_ranks = GameRankEx::find()->where(['<=', 'order', $game[0]['order']])
                ->andWhere(['gid' => $game[0]['game_id'], 'deleted_at' => 0])
                ->asArray()->all();

            $ranks = array_merge($ranks, $sub_ranks);

        }

        $rank_ids = ArrayHelper::getColumn($ranks, 'id');
        $zone_ids = ArrayHelper::getColumn($data, 'zone_id');
        //性别暂时去掉
        //$sex = UserCacheService::getUserInfo($uid, 'sex');
        $filter_ids = FeedbackService::getUserDefreindIds($uid);

        // 订单列表，分页后面要加上的
        $a = ['private_beater' => $uid, 'order_type' => self::$order_type_person];
        $ord = ReleaseOrder::find()
            ->where(['business_type' => self::$business_type])
            ->andWhere(['status' => self::$order_begin])
//            ->andWhere(['in', 'game', $game_mids])
            ->andWhere(['>=', 'create_time', $last10Min])
            ->andWhere(['not in', 'uid', $filter_ids])
            ->andWhere(['or', $a,
                //, 'game' => $game_mids],
                // 匹配订单的性别是无要求或者与自己性别相同，段位属于比自己低的，区服与自己相同
                ['receiver_id' => 0, 'order_type' => self::$order_type_all,
                    //'game_sex' => [0, $sex],
                    'game_rank' => $rank_ids, 'game_zone' => $zone_ids, 'game' => $match_game_mids]]);
                    //'game_rank' => $rank_ids, 'game_zone' => $zone_ids]]);
                //echo $ord->createCommand()->getRawSql();exit;


        //增加胜率筛选
        $i = 1;
        $victs = ['or', ['order_type' => self::$order_type_person, 'private_beater' => $uid]];
        foreach ($game_ids as $mid => $gid) {
            $victPromise = UserSkillService::userOpenVictoriousPromiseForSkill($uid, $gid);
            $vict = $victPromise ? 1 : 0;
            $victs[$i]['game'] = $mid;
            $victs[$i]['order_type'] = self::$order_type_all;
            $victs[$i]['victorious_promise'] = $vict;
            $i++;
        }
        //echo $ord->andWhere($victs)->createCommand()->getRawSql();exit;
        //$v = ['or', $victs];
        $ord = $ord->andWhere($victs);
        $orders =  $ord->orderBy('id desc')
            ->asArray()->all();

        // 把订单分类，分成私单和匹配单
        $orders = ArrayHelper::index($orders, null, 'order_type');

        // 把段位改成以段位 id 为key
        $ranks = ArrayHelper::index($ranks, null, 'id');

        $ret = self::$o_type;
        $time = time();

        foreach ($orders as $order_type => $sub_orders) {
            $order_type_flag = self::$order_types[0];
            if ($order_type == self::$order_type_all) {
                $order_type_flag = self::$order_types[1];
            }

            foreach ($sub_orders as $order) {
                $game_mid = $order['game'];
                $game_name = $games[$game_mid][0]['game'] ?? '';
                $rank_unit = $ranks[$order['game_rank']]['unit'] ?? '小时';

                $user_info = UserCacheService::getUserInfo($order['uid'], ['avator', 'nickname', 'sex', 'age']);
                $order_info = [];

                $order_info['order_id'] = $order['order_id'];
                $order_info['user_icon'] = $user_info['avator'];
                $order_info['order_type'] = $order_type;
                $order_info['nickname'] = $user_info['nickname'];
                $order_info['sex'] = $user_info['sex'];
                $order_info['age'] = $user_info['age'];
                $order_info['game'] = $game_name;
                $order_info['price'] = $order['really_price'];
                $order_info['num'] = $order['requirement_time'];
                $order_info['unit'] = $rank_unit;
                $order_info['victorious_promise'] = $order['victorious_promise'];
                $order_info['content'] = $order['content'] ?? '';
                $order_info['end_time'] = self::ORDER_VALID_TTL + $order['create_time'];
                $order_info['time_limit'] = (self::ORDER_VALID_TTL + $order['create_time'] - $time) > 0 ? (self::ORDER_VALID_TTL + $order['create_time'] - $time) : 0;

                if ($order_type == self::$order_type_all) {
                    $rank_name = $ranks[$order['game_rank']]['rank'] ?? '';
                    $rank_sub_title = $ranks[$order['game_rank']]['sub_title'] ?? '';
                    $zone_name = $games[$game_mid][0]['zone'] ?? '';

                    $order_info['zone'] = $zone_name;
                    $order_info['rank'] = $rank_name;
                    $order_info['demands'] = $rank_sub_title;
                } else {
                    $order_info['desc'] = "对您下了私人单";
                }

                $ret[$order_type_flag][] = $order_info;
            }
        }

        return $ret;

        /**
        //获取人物技能相关信息
        $data = (new \yii\db\Query())->select(['m.id','m.game_id','m.zone_id', 'm.rank_id', 'm.extension','mg.order', 'mg.unit','g.mid', 'g.game'])
            ->from("member_skill as m")
            ->where(['in', 'm.id', $skillIds])
            ->andWhere(['m.status' => MemberSkill::SKILL_STATUS_CERTIFIED])
            ->leftJoin("game_rank_ex mg", "m.rank_id=mg.id")
            ->leftJoin("game g", "g.id=m.game_id")
            ->all();


        $royal = '';
        foreach ($data as $k => $v) {
            $gids[$v['game_id']] = $v['mid'];
            if ($v['game'] == self::$game_royal) {
                $royal = $v['mid'];
                $royal_id = $v['game_id'];
            }

            $game_info[$v['mid']] = $v['order'];
            $game[$v['mid']] = $v;
        }

        //获取打手性别信息
        $profile = MemberProfile::find()->select('sex')->where(['uid' => $uid])->asArray()->one();
        $user_sex = $profile['sex'] ?? 0;

        //获取排位信息
        $gamerank = GameRankEx::find()->asArray()->all();
        $gr[0] = 0;
        foreach ($gamerank as $key => $value) {
            $gr[$value['id']] = $value['order'];
            $gr_info[$value['id']] = $value;
        }

        $time = time();
        $ti = $time - 600;
        //获取订单列表
        $orders = ReleaseOrder::find()
            ->where(['business_type' => self::$business_type])
            ->andWhere(['status' => self::$order_begin])
            ->andWhere(['in', 'game', $gids])
            ->andWhere(['>=', 'create_time', $ti])
            ->orderBy('id desc')
            ->asArray()
            ->all();

        if (isset($royal_id)) {
            $zones = GameZone::find()->where(['gid' => $royal_id])->asArray()->all();
            foreach ($zones as $k => $v) {
                if ($v['zone']) {
                    $zs = explode(' ', trim($v['zone']));
                    if (count($zs) == 1) {
                        $zone2[$v['id']] = trim($v['zone']);
                    } else {
                        $zone2[$v['id']] = $zs[2] ?? '';

                    }
                }

                if (in_array(trim($v['zone']), self::$zone_wx)) {
                    $zone1[0]['id'] = $v['id'];
                    $zone1[0]['zone'] = $v['zone'];
                }
                if (in_array(trim($v['zone']), self::$zone_qq)) {
                    $zone1[1]['id'] = $v['id'];
                    $zone1[1]['zone'] = $v['zone'];

                }
            }
            $z = [];
            foreach ($zone1 as $k => $v) {
                foreach ($v as $m => $n) {
                    $z[$n] = $v;
                }
            }
        }
        //筛选订单列表
        $ordreList = [];
        $ret = [];
        if ($orders) {
            foreach ($orders as $key => $order) {
                //游戏性别符合
                if ($order['game_sex'] == 0 || $user_sex == $order['game_sex']) {
                    //rank档位符合
                    if (isset($gr[$order['game_rank']]) && isset($game_info[$order['game']]) && $gr[$order['game_rank']] <= $game_info[$order['game']]) {
                        $uids[] = $order['uid'];
                        //zone空间符合
                        if ($order['game'] == $royal) {
                            if (isset($order['game_zone'])) {
                                if ($order['order_type'] == self::$order_type_all) {
                                    $ordreList[1][] = $order;
                                } elseif ($order['order_type'] == self::$order_type_person && $uid == $order['private_beater']) {

                                    $ordreList[0][] = $order;
                                }
                            }
                        } else {
                            if ($order['order_type'] == self::$order_type_all) {
                                $ordreList[1][] = $order;

                            } elseif ($order['order_type'] == self::$order_type_person && $uid == $order['private_beater']) {
                                $ordreList[0][] = $order;
                            }
                        }
                    }
                }
            }
            $ret = self::$o_type;
            if (is_array($uids) && !empty($uids)) {
                $uids = array_unique($uids);

                foreach ($uids as $k => $val) {
                    if ($val) {
                        $userProfile[$val] = UserCacheService::getUserInfo($val);
                    }
                }
                foreach ($ordreList as $k => $v) {
                    foreach ($v as $i => $j) {
                        if (isset($userProfile[$j['uid']])) {
                            //判断是否是私人单 不属于自己的私人单要过滤
                            if ($j['business_type'] == self::$business_type && $j['order_type'] == self::$order_type_person ) {
                                if ($j['private_beater'] != $uid) {
                                    continue;
                                }
                            }

                            $ret[self::$order_types[$k]][$i]['order_id'] = $j['order_id'];
                            $ret[self::$order_types[$k]][$i]['user_icon'] = $userProfile[$j['uid']]['avator'] ?? '';
                            $ret[self::$order_types[$k]][$i]['order_type'] = $j['order_type'];
                            $ret[self::$order_types[$k]][$i]['nickname'] = $userProfile[$j['uid']]['nickname'] ?? '';
                            $ret[self::$order_types[$k]][$i]['sex'] = $userProfile[$j['uid']]['sex'] ?? 0;
                            $ret[self::$order_types[$k]][$i]['age'] = $userProfile[$j['uid']]['age'] ?? 0;
                            $ret[self::$order_types[$k]][$i]['game'] = $game[$j['game']]['game'] ?? '';
                            $ret[self::$order_types[$k]][$i]['price'] = $j['really_price'];
                            $ret[self::$order_types[$k]][$i]['num'] = $j['requirement_time'];
                            $ret[self::$order_types[$k]][$i]['unit'] = $gr_info[$j['game_rank']]['unit'] ?? '小时';
                            $ret[self::$order_types[$k]][$i]['content'] = $j['content'];
                            //$ret[self::$order_types[$k]][$i]['time_limit'] = 600 - ($time - $j['create_time']);
                            $ret[self::$order_types[$k]][$i]['end_time'] = 600 + $j['create_time'];
                            $ret[self::$order_types[$k]][$i]['time_limit'] = (600 + $j['create_time'] - $time) > 0 ? (600 + $j['create_time'] - $time) : 0;
                            if ($j['order_type'] == self::$order_type_all) {
                                $ret[self::$order_types[$k]][$i]['zone'] = $zone2[$j['game_zone']] ?? '';
                                $ret[self::$order_types[$k]][$i]['rank'] = $gr_info[$j['game_rank']]['rank'] ?? '';
                                $ret[self::$order_types[$k]][$i]['demands'] = $gr_info[$j['game_rank']]['sub_title'] ?? '';
                            } else {
                                $ret[self::$order_types[$k]][$i]['desc'] = "对您下了私人单";

                            }
                        }

                    }

                    $ret[self::$order_types[$k]] = array_slice($ret[self::$order_types[$k]], 0);
                }
            }
        }
        return $ret;
        **/


    }

    //获取我的订单页面
    public static function getMyOrders($uid, $order_type, $page) {
        $phone = UserCacheService::getUserInfo($uid, 'phone');

        $count = 0;
        $orders = [];
        if ($order_type == self::$dl_order) {
            $count = ReleaseOrder::find()->where(['tm_phone' => $phone])->andWhere(['>', 'status', self::$zero])->count();
            $orders = ReleaseOrder::find()->where(['tm_phone' => $phone])->andWhere(['>', 'status', self::$zero])->limit(self::$limit)->offset(($page - 1) * self::$limit)->orderBy('id desc')->asArray()->all();
        }

        if ($order_type == self::$send_order) {
            $count = ReleaseOrder::find()->where(['uid' => $uid, 'business_type' => self::$business_type])->andWhere(['>', 'status', self::$zero])->count();
            $orders = ReleaseOrder::find()->where(['uid' => $uid, 'business_type' => self::$business_type])->andWhere(['>', 'status', self::$zero])->limit(self::$limit)->offset(($page - 1) * self::$limit)->orderBy('id desc')->asArray()->all();
        }

        if ($order_type == self::$receiver_order) {
            $count = ReleaseOrder::find()->where(['receiver_id' => $uid, 'business_type' => self::$business_type])->andWhere(['>', 'status', self::$zero])->count();
            $orders = ReleaseOrder::find()->where(['receiver_id' => $uid, 'business_type' => self::$business_type])->andWhere(['>', 'status', self::$zero])->limit(self::$limit)->offset(($page - 1) * self::$limit)->orderBy('id desc')->asArray()->all();
        }
        $game = GameService::getAllGame();
        foreach ($game as $k => $v) {
            $v['mid'] = empty($v['mid']) ? 0 : $v['mid'];
            $g[$v['mid']] = $v['game'];
        }

        $zone = GameService::getGameZone();
        foreach ($zone as $k => $v) {
            $zs = explode(' ', trim($v['zone']));
            if (count($zs) == 1) {
                $zo[$v['id']] = $v['zone'];
            } else {
                $zo[$v['id']] = $zs[2] ?? '';
            }
        }
        $rank = GameService::getGameRank();
        foreach ($rank as $k => $v) {
            $rk[$v['id']]['sub_title'] = $v['sub_title'];
            $rk[$v['id']]['unit'] = $v['unit'];

        }

        $o_list = [];
        if ($orders) {
            $uids = array_column($orders, 'uid');
            $uids = array_unique($uids);
            foreach ($uids as $k => $v) {
                $user_info[$v] = UserCacheService::getUserInfo($v);
            }
            $user_info[$uid] = UserCacheService::getUserInfo($uid);
            foreach ($orders as $k => $v) {
                if ($order_type == self::$dl_order) {
                    $v['uid'] = $uid;
                }
                $o_list[$k]['user_icon'] = $user_info[$v['uid']]['avator'] ?? '';
                $o_list[$k]['nickname'] = $user_info[$v['uid']]['nickname'] ?? '';
                $o_list[$k]['sex'] = $user_info[$v['uid']]['sex'] ?? 0;
                $o_list[$k]['age'] = $user_info[$v['uid']]['age'] ?? 0;
                $o_list[$k]['game'] = $g[$v['game']] ?? '';
                $o_list[$k]['oid'] = $v['order_id'];
                $o_list[$k]['status'] = (int)$v['status'];
                $o_list[$k]['status_cn'] = self::statusCn($v['status'], $order_type);
                $o_list[$k]['price'] = $v['price'];
                $o_list[$k]['num'] = $v['requirement_time'];
                $o_list[$k]['unit'] = $rk[$v['game_rank']]['unit'] ?? '小时';
                $o_list[$k]['rank'] = $rk[$v['game_rank']]['sub_title'] ?? '';
                $o_list[$k]['zone'] = $zo[$v['game_zone']] ?? '';
                $o_list[$k]['order_type'] = $v['order_type'];
                $o_list[$k]['victorious_promise'] = $v['victorious_promise'];

                //判断陪玩订单如果是代练相关的状态
                if (in_array($order_type, [self::$send_order, self::$receiver_order])) {

                    if ($v['status'] == 720) {
                        $status_map = MongoService::selectAll('order_status_record', ['order_id' => $v['order_id']], 'create_time asc', '', '');
                        $reason = '已关闭';
                        foreach ($status_map as $m => $n) {
                            $reason = $n['reason'];
                        }

                        if ($reason && in_array($reason, self::$close_sub_status)) {
                            $o_list[$k]['status'] = array_search($reason, self::$close_sub_status);

                        } else {
                            $o_list[$k]['status'] = 724;
                        }
                    }

                    if (in_array($v['status'], self::$appealing_status_all)) {
                        $o_list[$k]['status_cn'] = '申诉中';
                        $o_list[$k]['status'] = self::$order_appeal;
                    }

                    if (in_array($v['status'], self::$appealed_status_all)) {
                        $o_list[$k]['status_cn'] = '已处理';
                        $o_list[$k]['status'] = self::$appealed_status;
                    }
                }

                if ($order_type == self::$dl_order) {
                    $o_list[$k]['content'] = '订单: ' . ($v['title'] ?? '');

                } elseif ($order_type == self::$send_order) {
                    $o_list[$k]['content'] = $v['content'] ?? '';

                } elseif ($order_type == self::$receiver_order) {
                    $o_list[$k]['content'] = $v['content'] ?? '';
                    if ($v['order_type'] == self::$order_type_person) {
                        $o_list[$k]['order_type'] = 2;
                    }
                    
                } else {
                    $o_list[$k]['content'] = $v['content'] ?? '';
                }
            }
        }
        $data['count'] = $count;
        $data['data'] = $o_list;
        return $data;

    }


    public static function statusCn($status, $order_type) {
        if ($order_type == self::$dl_order) {
            if ($status == 200) {
                $status_cn = '已下单';
            } elseif ($status == 300) {
                $status_cn = '已接单';
            } elseif ($status == 400) {
                $status_cn = '代练中';
            }elseif (in_array($status, [500, 700, 720, 770])) {
                $status_cn = '已完成';
            } else {
                $status_cn = '申诉中';
            }

        } else {
            if ($status == 200) {
                $status_cn = '正在匹配';
            } elseif ($status == 300) {
                $status_cn = '已接单';
            } elseif ($status == 400) {
                $status_cn = '服务中';
            } elseif ($status == 500) {
                $status_cn = '待确认';
            } elseif ($status == 700) {
                $status_cn = '已确认';
            } elseif ($status == 720) {
                $status_cn = '已关闭';
            } elseif (in_array($status, [732, 733, 734, 735, 742, 743, 744, 745, 752, 753, 754, 755, 762, 763, 764, 765])) {
                $status_cn = '已处理';
            } elseif ($status == 800) {
                $status_cn = '已评论';
            } else {
                $status_cn = '申诉中';
            }

        }
        return $status_cn;
    }


    //获取订单详情
    public static function getOrderDetail($uid, $oid)
    {
        $phone = UserCacheService::getUserInfo($uid, 'phone');
        $order = ReleaseOrder::find()->where(['order_id' => $oid])->asArray()->one();

        $game = GameService::getAllGame();
        foreach ($game as $k => $v) {
            $v['mid'] = empty($v['mid']) ? 0 : $v['mid'];
            $g[$v['mid']] = $v['game'];
            $gid[$v['mid']] = $v['id'];
        }

        //判断如果是上分单
        if ($order['business_type'] != OrderService::$business_type) {
            $data = self::getOrderProcess($uid, $oid);
            return $data;
        }

        //判断该订单是否已被抢
        if ($order['order_type'] == OrderService::$order_type_all && $order['status'] != OrderService::$order_begin) {
            if ($order['receiver_id'] != $uid && $uid != $order['uid']) {
                return self::error(60002);
            }
        } elseif ($order['order_type'] == OrderService::$order_type_person) {
            if ($order['private_beater'] != $uid && $uid != $order['uid']) {
                return self::error(60002);
            }
        }

        /**
        if (!(in_array($uid, [$order['uid'], $order['private_beater']]) && $order['order_type'] == self::$order_type_person ) && !()) {

            //判断是不是上分单
            if (!(isset($order['tm_phone']) && $order['tm_phone'] && $phone == $order['tm_phone'])) {
                return self::error(60018);

            }
        }
         * **/

        $zone = GameService::getGameZone();
        foreach ($zone as $k => $v) {
            $zs = explode(' ', trim($v['zone']));
            if (count($zs) == 1) {
                $zo[$v['id']] = $v['zone'];
            } else {
                $zo[$v['id']] = $zs[2] ?? '';
            }
        }

        $rank = GameService::getGameRank();
        foreach ($rank as $k => $v) {
            $rk[$v['id']]['sub_title'] = $v['sub_title'];
            $rk[$v['id']]['unit'] = $v['unit'];
        }

        //判断当前用户是买家还是鱼俠
        if ($uid == $order['uid']) {
            $user_type = 1;
        } else {
            $user_type = 2;
        }
        
        $data['order']['status'] = $order['status'];
        $ti = time();
        $data['order']['order_id'] = $order['order_id'];
        $data['order']['order_type'] = $order['order_type'];
        $data['order']['user_type'] = $user_type;
        $data['order']['beaterId'] = $order['private_beater'];
        $data['order']['victorious_promise'] = $order['victorious_promise'];
        $data['order']['game']['game'] = $g[$order['game']] ?? '';
        $data['order']['game']['zone'] = $zo[$order['game_zone']] ?? '';
        $data['order']['game']['rank'] = $rk[$order['game_rank']]['sub_title'] ?? '';
        $data['order']['unit'] = $rk[$order['game_rank']]['unit'] ?? '小时';
        $data['order']['num'] = $order['requirement_time'];
        $data['order']['require_sex'] = $order['game_sex'];

        //判断该订单是否被申诉过
//        $data['order']['is_appeal'] = self::$zero;
//        $orderAppeal = OrderAppealRecord::find()->where(['order_id' => $order['order_id'], 'from_uid' => $uid])->asArray()->one();
//        if (!empty($orderAppeal)) {
//            $data['order']['is_appeal'] = 1;
//        }

        if ($order['requirement_time']) {
            $data['order']['unit_price'] = $order['price'] / $order['requirement_time'];
        } else {
            $data['order']['unit_price'] = 0;
        }
        $data['order']['price'] = $order['price'];
        $data['order']['content'] = $order['content'] ?? '';

        //获取用户信息
        $user[$order['uid']] = UserCacheService::getUserInfo($order['uid']);
        if ($order['private_beater']) {
            $user[$order['private_beater']] = UserCacheService::getUserInfo($order['private_beater']);
        }
        if ($order['receiver_id']) {
            $user[$order['receiver_id']] = UserCacheService::getUserInfo($order['receiver_id']);
        }

        //判断该订单是否被申诉过
        $data['order']['is_appeal'] = self::$zero;
        $status_map = MongoService::selectAll('order_status_record', ['order_id' => $order['order_id']], 'create_time asc', '', '');
        $c_time = 0;
        $day_time = 24 * 60 * 60;
        $reason = '已关闭';
        foreach ($status_map as $k => $v) {
            //判断是否发起过申诉
            if (!$data['order']['is_appeal'] && $uid == $v['uid'] && $v['reason'] == self::$appeal_reasopn) {
                $data['order']['is_appeal'] = self::$user_has_appeal;
            }

            $c_time = $v['create_time'];
            $reason = $v['reason'];
            if ($v['status'] == 200) {
                $data['order']['status_map'][$k]['user_icon'] = $user[$v['uid']]['avator'] ?? '';
                $data['order']['status_map'][$k]['status_cn'] = '已下单';
                $data['order']['status_map'][$k]['status'] = 200;
                $data['order']['status_map'][$k]['time'] = $v['create_time'];
            } elseif (in_array($v['status'], self::$appealing_status_all)) {
                $data['order']['status_map'][$k]['user_icon'] = $user[$v['uid']]['avator'] ?? '';
                $data['order']['status_map'][$k]['status_cn'] = '申诉中';
                $data['order']['status_map'][$k]['status'] = self::$order_appeal;
                $data['order']['status_map'][$k]['time'] = $v['create_time'];

            } elseif (in_array($v['status'], self::$appealed_status_all)) {
                $data['order']['status_map'][$k]['user_icon'] = $user[$v['uid']]['avator'] ?? '';
                $data['order']['status_map'][$k]['status_cn'] = '已处理';
                $data['order']['status_map'][$k]['status'] = self::$appealed_status;
                $data['order']['status_map'][$k]['time'] = $v['create_time'];

            }  elseif ($v['reason'] == '取消申请撤销') {
                $data['order']['status_map'][$k]['user_icon'] = $user[$v['uid']]['avator'] ?? '';
                $data['order']['status_map'][$k]['status_cn'] = '取消申诉';
                $data['order']['status_map'][$k]['status'] = $v['status'];
                $data['order']['status_map'][$k]['time'] = $v['create_time'];
            } else{
                $data['order']['status_map'][$k]['user_icon'] = $user[$v['uid']]['avator'] ?? '';
                $data['order']['status_map'][$k]['status_cn'] = $v['reason'];
                $data['order']['status_map'][$k]['status'] = $v['status'];
                $data['order']['status_map'][$k]['time'] = $v['create_time'];
            }

            if ($v['uid'] == self::$sys_uid) {
                $data['order']['status_map'][$k]['user_icon'] = UserCacheService::sysImg;
            }
        }
        $dealCount = 0;
        if ($user_type == self::$user_type_self && $order['private_beater']) {
            $gid[$order['game']] = $gid[$order['game']] ?? 3;
            $userSkill = UserSkillService::getUserSkills($order['private_beater'], $gid[$order['game']]);
            $user = UserCacheService::getUserInfo($order['private_beater'], ['avator', 'nickname', 'sex']);
            $data['user']['user_icon'] = $user['avator'] ?? '';
            $data['user']['nickname'] = $user['nickname'] ?? '';
            $data['user']['sex'] = $user['sex'] ?? 0;
            $data['user']['uid'] = $order['private_beater'];
            $dealCount = $userSkill['dealCount'] ?? 0;

        } elseif ($user_type == self::$user_type_self && empty($order['private_beater'])) {
            if (empty($order['receiver_id'])) {
                $data['user'] = new Object();
            } else {
                $gid[$order['game']] = $gid[$order['game']] ?? 3;
                $userSkill = UserSkillService::getUserSkills($order['receiver_id'], $gid[$order['game']]);
                $user = UserCacheService::getUserInfo($order['receiver_id'], ['avator', 'nickname', 'sex']);
                $data['user']['user_icon'] = $user['avator'] ?? '';
                $data['user']['nickname'] = $user['nickname'] ?? '';
                $data['user']['sex'] = $user['sex'] ?? 0;
                $data['user']['uid'] = $order['receiver_id'];
                $dealCount = $userSkill['dealCount'] ?? 0;
            }


        } elseif ($user_type == self::$order_type_person) {

            $gid[$order['game']] = $gid[$order['game']] ?? 3;
            $userSkill = UserSkillService::getUserSkills($order['uid'], $gid[$order['game']]);
            $user = UserCacheService::getUserInfo($order['uid'], ['avator', 'nickname', 'sex']);
            $data['user']['user_icon'] = $user['avator'] ?? '';
            $data['user']['nickname'] = $user['nickname'] ?? '';
            $data['user']['sex'] = $user['sex'] ?? 0;
            $data['user']['uid'] = $order['uid'];
            $dealCount = $userSkill['dealCount'] ?? 0;

        } else {
            $data['user'] = new Object();
        }

        if ($order['order_type'] == self::$order_type_person && $user_type == self::$user_type_self) {
            $data['user']['dealCount_desc'] = '接单' . $dealCount . '次';
        }

        if ($order['order_type'] == self::$order_type_person && $user_type == self::$user_type_pw) {
            $count = ReleaseOrder::find()->where(['uid'=>$order['uid'], 'private_beater' => $uid])->count();
            $data['user']['dealCount_desc'] = '对你下单' . $count . '次';
        }
        //匹配单未接单
        if ($order['status'] == 200) {
            $time_limit = 600 - ($ti - $order['create_time']);
            $data['order']['sub_status']['time_limit'] = ($time_limit > 0) ? $time_limit : 0;
            $data['order']['sub_status']['create_time'] = $c_time;

            if ($order['order_type'] == self::$order_type_all) {
                $data['order']['status_cn'] = "正在匹配";
                $data['order']['sub_status']['type'] = 1;

            } else {
                $data['order']['status_cn'] = "已下单";
                $data['order']['sub_status']['type'] = 3;

            }

        } elseif ($order['status'] == 300) {
            if ($uid != $order['uid'] && $uid != $order['receiver_id']) {
                $data['order']['status'] = 301;
            }

            $time_limit = $day_time - ($ti - $order['create_time']);
            $data['order']['status_cn'] = "已接单";
            $data['order']['sub_status']['time_limit'] = ($time_limit > 0) ? $time_limit : 0;
            $data['order']['sub_status']['create_time'] = $c_time;
            $data['order']['sub_status']['type'] = 8;

        } elseif ($order['status'] == 400) {

            $time_limit = $day_time - ($ti - $order['create_time']);
            $data['order']['status_cn'] = "服务中";
            $data['order']['sub_status']['time_limit'] = ($time_limit > 0) ? $time_limit : 0;
            $data['order']['sub_status']['create_time'] = $c_time;
            $data['order']['sub_status']['type'] = 8;

        } elseif ($order['status'] == 500) {

            $time_limit = $day_time - ($ti - $order['create_time']);
            $data['order']['status_cn'] = "待确认";
            $data['order']['sub_status']['time_limit'] = ($time_limit > 0) ? $time_limit : 0;
            $data['order']['sub_status']['create_time'] = $c_time;
            $data['order']['sub_status']['type'] = 8;

        } elseif ($order['status'] == 700) {
            $data['order']['status_cn'] = "已确认";
            $data['order']['sub_status']['time_limit'] = 0;
            $data['order']['sub_status']['create_time'] = $c_time;

        } elseif ($order['status'] == 720) {

            if ($reason && in_array($reason, self::$close_sub_status)) {
                $data['order']['status'] = array_search($reason, self::$close_sub_status);
            } else {
                $data['order']['status'] = 724;
            }

            $data['order']['status_cn'] = $reason;
            $data['order']['sub_status']['time_limit'] = 0;
            $data['order']['sub_status']['create_time'] = $c_time;

        } elseif ($order['status'] == 800) {
            $data['order']['status_cn'] = "已评价";
            $data['order']['sub_status']['time_limit'] = 0;
            $data['order']['sub_status']['create_time'] = $c_time;

        } elseif (in_array($order['status'], self::$appealing_status_all)) {
            $data['order']['status'] = self::$order_appeal;
            $data['order']['status_cn'] = '申诉中';
            $time_limit = $day_time - ($ti - $order['create_time']);
            $appeal = OrderAppealService::detailAboutAppeal($uid, $order['order_id']);
            $appeal_status = $appeal['appeal_status'] ?? '';
            $data['order']['sub_status']['type'] = array_search($appeal_status, self::$sub_status);
            $data['order']['sub_status']['create_time'] = $c_time;
            $data['order']['sub_status']['time_limit'] = ($time_limit > 0) ? $time_limit : 0;
            /**
            if ($order['status'] == 735) {
                $data['order']['sub_status']['appeal']['content'] = $appeal['content'];
                $data['order']['sub_status']['appeal']['buy_nickname'] = $appeal['money'][0]['nickname'];
                $data['order']['sub_status']['appeal']['buy_money'] = $appeal['money'][0]['money'];
                $data['order']['sub_status']['appeal']['rec_nickname'] = $appeal['money'][1]['nickname'];
                $data['order']['sub_status']['appeal']['rec_money'] = $appeal['money'][1]['money'];

            }
             * **/
        }  elseif (in_array($order['status'], self::$appealed_status_all)) {
            $data['order']['status'] = self::$appealed_status;
            $data['order']['status_cn'] = '已处理';
            $time_limit = $day_time - ($ti - $order['create_time']);
            $appeal = OrderAppealService::detailAboutAppeal($uid, $order['order_id']);
            $appeal_status = $appeal['appeal_status'] ?? '';
            $data['order']['sub_status']['type'] = array_search($appeal_status, self::$sub_status);
            $data['order']['sub_status']['create_time'] = $c_time;
            $data['order']['sub_status']['time_limit'] = ($time_limit > 0) ? $time_limit : 0;
            $data['order']['sub_status']['appeal']['content'] = $appeal['content'] ?? '';
            $data['order']['sub_status']['appeal']['buy_nickname'] = $appeal['money'][0]['nickname'] ?? '';
            $data['order']['sub_status']['appeal']['buy_money'] = $appeal['money'][0]['money'] ?? 0;
            $data['order']['sub_status']['appeal']['rec_nickname'] = $appeal['money'][1]['nickname'] ?? '';
            $data['order']['sub_status']['appeal']['rec_money'] = $appeal['money'][1]['money'] ?? 0;

        }


        return $data;
    }

    /**
     * @param array $orderinfo
     * @return array
     */
    public static function getOrderProcess($buyer, $order_id)
    {
        $orderinfo = ReleaseOrder::find()->where(['order_id' => $order_id])->asArray()->one();
        $buyer_phone = $orderinfo['tm_phone'];
        $phone_user = Member::find()->where(['phone' => $buyer_phone])->asArray()->one();
        if ($buyer == 0) {
            $buyer = $phone_user['id'] ?? 0;
        }
        if ($buyer != $phone_user['id'] || $buyer == 0) {
            return array();
        }

        $buyer_info = UserCacheService::getUserInfo($buyer);
        $orderinfo['buyer_avator'] = $buyer_info['avator'];
        $orderinfo['buyer_nickname'] = $buyer_info['nickname'];
        $orderinfo['game'] = MongoService::selectOne('pc_game', ['_id' => $orderinfo['game']])['name']??'';
        $orderinfo['game_zone'] = MongoService::selectOne('pc_game', ['_id' => $orderinfo['game_zone']])['name']??'';
        $orderinfo['game_server'] = MongoService::selectOne('pc_game', ['_id' => $orderinfo['game_server']])['name']??'';
        //下单进程
        $orderinfo['process'][0] = ['time' => (int)$orderinfo['create_time'], 'type' => 200, 'status' => '下单', 'avator' => $buyer_info['avator'], 'nickname' => $buyer_info['nickname']];
        if (empty($orderinfo['receiver_id'])) {
            return $orderinfo;
        }
        $receiver_info = UserCacheService::getUserInfo($orderinfo['receiver_id'], ['avator', 'nickname', 'lol_star', 'wzry_star']);

        if (empty($receiver_info)) {
            return $orderinfo;
        }

        //接单进程
        if (!empty($orderinfo['receiver_time'])) {
            $orderinfo['receiver_avator'] = $receiver_info['avator'];
            $orderinfo['receiver_nickname'] = $receiver_info['nickname'];
            if ($orderinfo['game'] == '英雄联盟') {
                $orderinfo['star'] = (string)$receiver_info['lol_star'];
            } else {
                $orderinfo['star'] = (string)$receiver_info['wzry_star'];
            }
            $orderinfo['process'][1] = ['time' => (int)$orderinfo['receiver_time'], 'type' => 300, 'status' => '已接单',  'avator' => $orderinfo['receiver_avator'], 'nickname' => $orderinfo['receiver_nickname']];
            $orderinfo['receive_speed'] = $orderinfo['receiver_time'] - $orderinfo['create_time'];
        }

        //打单进程
        $dailianing = MongoService::selectOne('sdy_message', ['type' => 1, 'order_id' => $orderinfo['order_id']]);
        if ($dailianing) {
            $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => (int)$dailianing['sendtime'], 'type' => 400, 'status' => '进行中',  'avator' => $orderinfo['receiver_avator'], 'nickname' => $orderinfo['receiver_nickname']]]);
        }

        //客服处理进程
        $kefu = MongoService::selectAll('order_status_record', ['before_status' => [300, 400, 500], 'status' => [310, 331, 410, 431, 510, 531, 610, 631, 312, 320, 321, 332, 620, 612, 621, 632, 520, 512, 532, 521, 420, 412, 421, 432, 600, 330, 430, 530, 630, 440, 340], 'order_id' => $orderinfo['order_id']]);
        if ($kefu) {
            foreach ($kefu as $k => $v){
                $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => $v['create_time'], 'type' => 800, 'status' => '争议处理',  'avator' => SELF::LOGO, 'nickname' => '闪电鱼']]);
            }
        }

        //退出客服进正常进程
        $quit = MongoService::selectAll('order_status_record', ['before_status' => [310, 331, 410, 431, 510, 531, 610, 631, 312, 320, 321, 332, 620, 612, 621, 632, 520, 512, 532, 521, 420, 412, 421, 432, 600, 330, 430, 530, 630, 440, 340], 'order_id' => $orderinfo['order_id'], 'status' => [300, 400, 500]]);
//        if ($quit) {
            foreach ($quit as $m => $n){
                $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => $n['create_time'], 'type' => $n['status'], 'status' => '进行中',  'avator' => SELF::LOGO, 'nickname' => '闪电鱼']]);
            }

            //待验收进程
            if (!empty($orderinfo['apply_time'])) {
                $orderinfo['doing_speed'] = $orderinfo['apply_time'] - $orderinfo['receiver_time'];
                $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => (int)$orderinfo['apply_time'], 'type' => 500, 'status' => '已完成',  'avator' => $orderinfo['receiver_avator'], 'nickname' => $orderinfo['receiver_nickname']]]);
            }

//            //已完成进程
//            if (!empty($orderinfo['finish_time'])) {
//                $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => (int)$orderinfo['apply_time'], 'type' => 700, 'avator' => $orderinfo['receiver_avator'], 'nickname' => $orderinfo['receiver_nickname']]]);
//            }

            //评价进程
            $comment = OrderComment::findOne(['order_id' => $orderinfo['order_id']]);
            $orderinfo['have_comment'] = 0;
            if ($comment) {
                $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => $comment->time, 'type' => 600, 'status' => '已评价',  'avator' => $orderinfo['buyer_avator'], 'nickname' => $orderinfo['buyer_nickname']]]);
                $orderinfo['have_comment'] = 1;
            }

            //胜负战绩
            $exploits = MongoService::selectOne('order_exploits', ['order_id' => $orderinfo['order_id']]);
            $orderinfo['success'] = 0;
            $orderinfo['fail'] = 0;
            if ($exploits) {
                $orderinfo['exploits'] = explode(",", $exploits['exploits']);
                foreach ($orderinfo['exploits'] as $key => $value) {
                    if ($value == 1) {
                        $orderinfo['success']++;
                    } else {
                        $orderinfo['fail']++;
                    }
                }
            } else {
                $orderinfo['exploits'] = array();
            }

            //上下号截图
            $up = MongoService::selectAll('order_screenshot', ['order_id' => $orderinfo['order_id'], 'type' => 1], null, ['screenshot']);
            $down = MongoService::selectAll('order_screenshot', ['order_id' => $orderinfo['order_id'], 'type' => 2], null, ['screenshot']);
            $orderinfo['up_screenshot'] = empty($up) ? array() : $up;
            $orderinfo['down_screenshot'] = empty($down) ? array() : $down;

            $orderinfo['dynamic_id'] = 0;
//            $dynamic_video = DynamicVideo::findOne(['order_id' => $orderinfo['order_id']]);
//            if ($dynamic_video) {
//                $dynamic = Dynamic::findOne(['video_id' => $dynamic_video->id]);
//                if ($dynamic) {
//                    $orderinfo['dynamic_id'] = $dynamic->id;
//                }
//            }
//        }

        //退出客服进关闭进程
        $quit = MongoService::selectOne('order_status_record', ['before_status' => [310, 331, 410, 431, 510, 531, 610, 631, 312, 320, 321, 332, 620, 612, 621, 632, 520, 512, 532, 521, 420, 412, 421, 432, 600, 330, 430, 530, 630, 440, 340], 'order_id' => $orderinfo['order_id'], 'status' => [730, 731, 760, 761, 750, 751, 740, 741, 733, 732, 735, 734, 762, 763, 765, 764, 752, 753, 754, 755, 742, 743, 744, 745, 1000]]);
        if ($quit) {
            $orderinfo['process'] = array_merge($orderinfo['process'], [0 => ['time' => $quit['create_time'], 'type' => 900, 'status' => '已处理',  'avator' => SELF::LOGO, 'nickname' => '闪电鱼']]);
        }

        $orderinfo['process'] = ArrayUtil::array_sort($orderinfo['process'], 'time');
        return $orderinfo;
    }



    /**
     * @param int $type 0 普通+操作 消息  1  申请撤销  2 提交异常  3 申请仲裁  4 客服仲裁
     * @param int $uid 发送人
     * @param int $touid 接收人
     * @param int $costomer_id 客服id
     * @param string $order_id 订单号
     * @param int $is_buyer 是否是买家
     * @param int $status 状态码
     * @param string $comment 留言
     * @param string $type_msg 操作
     * @param double $part_train_fee 商家愿意支付的部分代练费
     * @param double $part_deposit 打手愿意支付的部分保证金
     * @param double $merchant_arbitration_money 仲裁商家获得保证金
     * @param double $beater_arbitration_money 仲裁打手获得代练费
     * @param int $menber
     * @param int $uid_is_read
     **/
    public static function insertpcmsg($type, $uid, $touid, $order_id, $is_buyer, $status, $comment, $type_msg = '', $part_train_fee = 0,
                                $part_deposit = 0, $merchant_arbitration_money = 0, $beater_arbitration_money = 0, $menber = '', $applicant = '')
    {
        $message = [];
        $message['type'] = intval($type);
        $message['uid'] = intval($uid);
        $message['touid'] = intval($touid);
        $message['order_id'] = $order_id;
        $message['is_buyer'] = $is_buyer;
        $message['status'] = intval($status);
        $message['comment'] = $comment;
        $message['create_time'] = time();
        $message['is_delete'] = 0;
        $message['is_read'] = 0;
        if ($type == 0) {
            $message['type_msg'] = $type_msg;
        } elseif ($type == 1) {
            $message['type_msg'] = '申请撤销：';
            $message['part_train_fee'] = floatval(sprintf('%.2f', $part_train_fee));
            $message['part_deposit'] = floatval(sprintf('%.2f', $part_deposit));
        } elseif ($type == 2) {
            $message['type_msg'] = '提交了异常：';
        } elseif ($type == 3) {
            $message['type_msg'] = '申请了仲裁：';
        } elseif ($type == 4) {
            $message['merchant_arbitration_money'] = $merchant_arbitration_money;
            $message['beater_arbitration_money'] = $beater_arbitration_money;
            $message['responsible_party_id'] = $menber;
            $message['applicant'] = $applicant;
            $message['type_msg'] = $type_msg ? $type_msg : '仲裁说明：';
        }
        if (MongoService::insert('pc_message', $message)) {
            return $message;
        }
        return false;
    }

    public static function getRelationalUser($uid)
    {
        $data = ReleaseOrder::find()->where(['uid' => $uid, 'business_type' => 5])->andWhere(['not in', 'status' , [200,700,720,800,731,721,0,745]])->asArray()->all();
        $arr = array_column($data, 'receiver_id');

        $data = ReleaseOrder::find()->where(['receiver_id' => $uid, 'business_type' => 5])->andWhere(['not in', 'status' , [200,700,720,800,731,721,0,745]])->asArray()->all();
        $arr = array_merge($arr, array_column($data, 'uid'));
        return $arr;
    }

    //获取2个用户有订单关系的订单
    public static function getUsersRel($uid1, $uid2) {
        $ord1 = ReleaseOrder::find()
            ->where(['>=', 'status', 300])
            ->andWhere(['<', 'status', 700])
            ->andWhere(['business_type' => 5])
            ->andWhere(['or', ['uid' => $uid1, 'receiver_id' => $uid2], ['uid' => $uid2, 'receiver_id' => $uid1]])
            ->andWhere([])
            ->asArray()
            ->all();
        $ord2 = ReleaseOrder::find()
            ->where(['status' => 200, 'business_type' => 5])
            ->andWhere(['or', ['uid' => $uid1, 'receiver_id' => $uid2], ['uid' => $uid2, 'receiver_id' => $uid1]])
            ->asArray()
            ->all();
        $ord = array_merge($ord1, $ord2);
        $orders = ArrayUtil::array_sort($ord, 'id', 'desc');

        $ret = [];
        foreach ($orders as $key => $val) {
            $status_map = MongoService::selectAll('order_status_record', ['order_id' => $val['order_id']], 'create_time asc', '', '');
            $reason = '已关闭';
            foreach ($status_map as $k => $v) {
                $reason = $v['reason'];
            }

            $ret[$key]['order_id'] = $val['order_id'];
            $ret[$key]['user_type'] = ($uid1 == $val['uid']) ? 1 : 2;//1:用户  2:鱼俠
            $ret[$key]['status'] = $val['status'];
            if (in_array($val['status'], self::$appealing_status_all)) {
                $ret[$key]['status'] = self::$order_appeal;

            }
            if ($val['status'] == 720) {
                if ($reason && in_array($reason, self::$close_sub_status)) {
                    $ret[$key]['status'] = array_search($reason, self::$close_sub_status);
                } else {
                    $ret[$key]['status'] = 724;
                }
            }
            $ret[$key]['game'] = $val['game'];
        }
        return $ret;
    }




}