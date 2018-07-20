<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/20
 * Time: 14:38
 */

namespace Api\services\v1;


use common\base\BaseService;
use common\models\orm\OrderAppealRecord;
use common\models\orm\ReleaseOrder;
use common\models\utils\FuncUtil;
use Yii;
use yii\db\Exception;
use yii\db\Query;

class OrderAppealService extends BaseService
{
    public static $errors = [
        70010 => '订单状态已变更，请刷新重试',
        70009 => '订单状态更新失败',
        70008 => '订单状态值异常',
        70007 => '申诉提交失败',
        70006 => '申诉方重复提交',
        70005 => '该订单没有申诉记录',
        70004 => '您无权放弃该订单的申诉',
        70003 => '放弃申诉失败，请稍后再试',
        70002 => '被申诉方重复提交',

    ];

    CONST LOGO = "http://flashfish.oss-cn-hangzhou.aliyuncs.com/CDN/Images/avator/newlogo.png";

    public static function submitAppeal($uid, $data)
    {
        $orderinfo = ReleaseOrder::find()->where(['order_id' => $data['order_id']])->one();
        if (in_array($orderinfo->status, [732,733,734,735,762,763,764,765,752,753,754,755,742,743,744,745,700])) {
            self::error(70010);
        }
        $info = OrderAppealRecord::find()->where(['order_id' => $data['order_id']])->one();
        if ($info && $info->deleted_at == 0) {
            if ($info->from_uid == $uid) {
                self::error(70006);
            }
            if ($info->status == 2) {
                self::error(70002);
            }
            $info->to_content = $data['content'];
            $info->to_pic = $data['file'];
            $info->to_at = time();
            $info->status = 2;
            if ($info->save()){
                $status = $orderinfo->status;
                self::insertMongoOrderStatusRecord($uid, $info->from_uid, $data['order_id'], intval($status / 100).'21', '申请仲裁', 0, $status, $data['file']??'', 1);
                $status = intval($status / 100).'21';
                $orderinfo->status = $status;
                $orderinfo->save();
                return true;
            }
            self::error(70007);
        } elseif ($info && $info->deleted_at != 0) {
            if ($info->from_uid == $uid) {
                self::error(70006);
            } else {
                if (MongoService::selectOne('order_status_record', ['order_id' => $data['order_id'], 'uid' => (int)$uid, 'reason' => '取消申请撤销'])){
                    self::error(70006);
                }
            }
            $to_uid = $info->from_uid;
            $info->from_uid = $uid;
            $info->from_content = $data['content']??'';
            $info->from_pic = $data['file']??'';
            $info->from_at = time();
            $info->deleted_at = 0;
            $info->status = 1;
            $info->to_status = 1;
            $info->to_content = '';
            $info->to_pic = '';
            $info->to_at = time();
            $info->to_uid = $to_uid;
            if ($info->save()) {
                $status = $orderinfo->status;
                self::insertMongoOrderStatusRecord($uid, $to_uid, $data['order_id'], intval($status / 100).'31', '申请撤销', 0, $status, $data['file']??'', 1);
                $status = intval($status / 100).'31';
                $orderinfo->status = $status;
                $orderinfo->save();
                MessageService::changeOrderStatePush($info->to_uid, $uid, Yii::$app->params['push_code']['appeal'], $data['order_id']);
                return true;
            }
            self::error(70007);
        }
        if ($orderinfo->uid == $uid) {
            $to_uid = $orderinfo->receiver_id;
        } else {
            $to_uid = $orderinfo->uid;
        }
        $info = new OrderAppealRecord();
        $info->order_id = $data['order_id'];
        $info->created_at = time();
        $info->from_uid = $uid;
        $info->from_content = $data['content'];
        $info->from_pic = $data['file'];
        $info->from_at = time();
        $info->to_uid = $to_uid;
        if ($info->save()) {
            $status = $orderinfo->status;
            self::insertMongoOrderStatusRecord($uid, $to_uid, $data['order_id'], intval($status / 100).'31', '申请撤销', 0, $status, $data['file']??'', 1);
            $status = intval($status / 100).'31';
            $orderinfo->status = $status;
            $orderinfo->save();
            MessageService::changeOrderStatePush($info->to_uid, $uid, Yii::$app->params['push_code']['appeal'], $data['order_id']);
            return true;
        }
        self::error(70007);
    }

    public static function time($time)
    {
        if ($time>3600) {
            $hour = floor($time/3600);
            $minute = floor(($time-$hour*3600)/60);
            return $hour.'小时'.$minute.'分';
        } else {
            $minute = floor($time/60);
            return $minute.'分';
        }
    }

    private static function getPicUrl($info)
    {
        if (empty($info)) {
            return array();
        }
        $pic = explode(',', $info);
        if (!empty($pic)) {
            foreach ($pic as $k => $v) {
                $pic[$k] = FuncUtil::getAbsoluteUri($v);
            }
        } else {
            return array();
        }
        return $pic;
    }

    public static function appealDetail($uid, $order_id)
    {
        $info = OrderAppealRecord::find()->where(['order_id' => $order_id])->one();
        if (!$info) {
            self::error(70005);
        }
        $from_info = UserCacheService::getUserInfo($info->from_uid,['nickname', 'avator']);
        $to_info = UserCacheService::getUserInfo($info->to_uid,['nickname', 'avator']);

        $data['list'][0] = [
            'avator' => $from_info['avator'],
            'nickname' => $from_info['nickname'],
            'time' => $info->from_at,
            'content' => $info->from_content,
            'pic' => self::getPicUrl($info->from_pic),
            'describe' => '申诉方提交资料'
        ];
        if ($info->status == 1){
            $time_rest = time()-$info->created_at;
            if ($time_rest > 24*60*60) {
                $descibe ='被申诉方提交超时';
                $time = '';
            } else {
                $rest = 24*60*60 - $time_rest;
                $descibe = '被申诉方提交资料';
                $time =  '剩余 ' . self::time($rest);
            }
            if ($uid == $info->to_uid) {
                $code = 2; //申诉按钮
            } else {
                $code = 1; //取消申诉按钮
            }
            $data['info'][0] = ['avator' => $from_info['avator'], 'descibe' => '申诉方提交资料', 'is_selected' => 0];
            $data['info'][1] = ['avator' => $to_info['avator'], 'descibe' => $descibe, 'is_selected' => 1, 'time' => $time];
            $data['info'][2] = ['avator' => SELF::LOGO, 'descibe' => '客服仲裁', 'is_selected' => 0];
        } else {
            $data['info'][0] = ['avator' => $from_info['avator'], 'descibe' => '申诉方提交资料', 'is_selected' => 0];
            $data['info'][1] = ['avator' => $to_info['avator'], 'descibe' => '被申诉方提交资料', 'is_selected' => 0];
            $data['info'][2] = ['avator' => SELF::LOGO, 'descibe' => '客服仲裁', 'is_selected' => 1];
            if ($info->to_status == 1) {
                $data['list'][1] = [
                    'avator' => $to_info['avator'],
                    'nickname' => $to_info['nickname'],
                    'time' => $info->to_at,
                    'content' => $info->to_content,
                    'pic' => self::getPicUrl($info->to_pic),
                    'describe' => '被申诉方提交资料'
                ];
                if ($uid == $info->to_uid && $info->status == 2) {
                    $code = 3; //空白
                } else {
                    $code = 1; //取消申诉按钮
                }
            } elseif ($info->to_status == 2) {
                $data['list'][1] = [
                    'avator' => $to_info['avator'],
                    'nickname' => $to_info['nickname'],
                    'time' => $info->to_at,
                    'content' => $info->to_content,
                    'pic' => self::getPicUrl($info->to_pic),
                    'describe' => '被申诉方放弃提交'
                ];
                if ($uid == $info->to_uid) {
                    $code = 3; //空白
                } else {
                    $code = 1; //取消申诉按钮
                }
            } else {
                $data['list'][1] = [
                    'avator' => $to_info['avator'],
                    'nickname' => $to_info['nickname'],
                    'time' => '',
                    'content' => $info->to_content,
                    'pic' => self::getPicUrl($info->to_pic),
                    'describe' => '被申诉方提交超时'
                ];
                if ($uid == $info->to_uid) {
                    $code = 3; //空白
                } else {
                    $code = 1; //取消申诉按钮
                }
            }
            if ($info->status == 3) {
                $money[0] = [
                    'nickname' => $from_info['nickname'],
                    'money' => $info->from_money
                ];
                $money[1] = [
                    'nickname' => $to_info['nickname'],
                    'money' => $info->to_money
                ];
                $data['list'][2] = [
                    'avator' => SELF::LOGO,
                    'nickname' => '客服仲裁结果',
                    'time' => $info->finish_at,
                    'content' => $info->appeal_content,
                    'money' => $money
                ];
                $code = 3; //无按钮
                $data['result'] = [
                    'from_nickname' => $from_info['nickname'],
                    'from_money' => $info->from_money,
                    'to_nickname' => $to_info['nickname'],
                    'to_money' => $info->to_money,
                    'time' => $info->finish_at,
                    'content' => $info->appeal_content,
                ];
            }
        }
        $data['code'] = $code;
        return $data;

    }

    public static function detailAboutAppeal($uid, $order_id)
    {
        $order_info = OrderAppealRecord::find()->where(['order_id' => $order_id])->one();
        if (empty($order_info)) {
            return array();
        }
        if ($order_info->status == 1) {
            if ($uid == $order_info->from_uid) {
                $time = 60*60*24-(time()-$order_info->from_at);
                $data['appeal_status'] = '对方上传申诉资料';
                $data['time'] = '剩余'.self::time($time);
                $data['content'] = '';
            } else {
                $time = 60*60*24-(time()-$order_info->from_at);
                $data['appeal_status'] = '我方上传申诉资料';
                $data['time'] = '剩余'.self::time($time);
                $data['content'] = '';
            }
        } elseif ($order_info->status == 2) {
            $data['appeal_status'] = '等待客服仲裁';
            $data['time'] = '';
            $data['content'] = '';
        } else {
            $data['appeal_status'] = '客服仲裁结果';
            $data['time'] = date('Y-m-d H:i:s',$order_info->finish_at);
            $data['content'] = $order_info->appeal_content??'';
            $from_info = UserCacheService::getUserInfo($order_info->from_uid,['nickname']);
            $to_info = UserCacheService::getUserInfo($order_info->to_uid,['nickname']);
            $money[0] = [
                'nickname' => $from_info['nickname'],
                'money' => $order_info->from_money
            ];
            $money[1] = [
                'nickname' => $to_info['nickname'],
                'money' => $order_info->to_money
            ];
            $data['money'] = $money;
        }
        return $data;
    }

    /**状态装换算法
     * @param $type 1->买家   2->打手    3->发单人   4->后台
     * @param $operate 1->同意   2->撤销订单    3->仲裁     4->取消操作（取消申请仲裁，取消申请撤销，取消锁定账号）  5->发单人锁定
     * 6->打手开始打单  7->打手 打完单  8->打手提交异常  9->买家确认收货
     * 10->买家付款             11->商家发单    12->发单人删除   13->商家修改
     * 14->商家下架       15->打手接单    16->买家评价  17->买家删除订单
     * @param $status 当前订单的状态
     */
    public static function generateStatus($type, $operate, $status)
    {
        $hundred = intval($status / 100);
        $hundreds = $hundred * 100;
        $remainder = $status % 100;
        if ($status == 0) {
            if ($type == 1 && $operate == 10) {
                return 100;
            } elseif ($type == 1 && $operate == 2) {
                return 40;
            } elseif ($type == 1 && $operate == 17) {
                return -1;
            }
        }
        if ($status == 100) {
            if ($type == 3 && $operate == 11) {
                return 200;
            } elseif ($type == 1 && $operate == 2) {
                return 140;
            } elseif ($type == 3 && $operate == 2) {
                return 710;
            }
        }
        if ($status == 140) {
            if ($type == 3 && $operate == 11) {
                return 200;
            } elseif ($type == 3 && $operate == 2) {
                return 710;
            } elseif ($type == 1 && $operate == 4) {
                return 100;
            }
        }
        if ($status == 200) {
            if ($type == 3 && $operate == 12) {
                return 720;
            } elseif ($type == 3 && $operate == 13) {
                return 160;
            } elseif ($type == 3 && $operate == 14) {
                return 150;
            } elseif ($type == 2 && $operate == 15) {
                return 300;
            } elseif ($type == 1 && $operate == 2) {
                return 240;
            } elseif ($type == 2 && $operate == 2) {
                return 201;
            } elseif ($type == 3 && $operate == 2) {
                return 720;
            }
        }
        if ($status == 240) {
            if ($type == 3 && $operate == 11) {
                return 200;
            } elseif ($type == 3 && $operate == 2) {
                return 720;
            } elseif ($type == 1 && $operate == 4) {
                return 200;
            }
        }
        if ($status == 201 && $type == 3 && $operate == 11) {
            return 200;
        }
        if ($status == 300 && $type == 2 && $operate == 6) {
            return 400;
        } elseif ($status == 300 && $type == 2 && $operate == 8) {
            return 600;
        }
        if ($status == 400) {
            if ($type == 2 && $operate == 7) {
                return 500;
            } elseif ($type == 2 && $operate == 8) {
                return 600;
            }
        }
        if ($status == 500) {
            if ($type == 1 && $operate == 9) {
                return 700;
            } elseif ($type == 3 && $operate == 9) {
                return 700;
            } elseif ($type == 2 && $operate == 4) {
                return 400;
            }
        }
        if ($status == 600) {
            if ($type == 2 && $operate == 4) {
                return 400;
            }
        }
        if ($status == 700 && $type == 1 && $operate == 16) {
            return 770;
        }
        if ($hundred == 3 || $hundred == 4 || $hundred == 5 || $hundred == 6) {
            if ($remainder == 0) {
                if ($type == 3 && $operate == 5) {//300->330
                    return $hundreds + 30;
                } elseif ($type == 3 && $operate == 2) {//300->310
                    return $hundreds + 10;
                } elseif ($type == 2 && $operate == 2) {//300->331
                    return $hundreds + 31;
                } elseif ($type == 1 && $operate == 2) {//300->340
                    return $hundreds + 40;
                }
            }

            if ($remainder == 31) {
                if ($type == 3 && $operate == 1) { //331->731
                    return 700 + $hundred * 10 + 1;
                } elseif ($type == 3 && $operate == 3) {//331->321
                    return $hundreds + 21;
                } elseif ($type == 2 && $operate == 4) {//331->330
                    return $hundreds + 30;
                } elseif ($type == 2 && $operate == 3) {//331->332
                    return $hundreds + 32;
                }

            }
            if ($remainder == 10) {
                if ($type == 3 && $operate == 4) {//310->330
                    return $hundreds + 30;
                } elseif ($type == 3 && $operate == 3) {//310->320
                    return $hundreds + 20;
                } elseif ($type == 2 && $operate == 1) {//310->730
                    return 700 + $hundred * 10;
                } elseif ($type == 2 && $operate == 3) {//310->312
                    return $hundreds + 12;
                }
            }
            if ($remainder == 20) {
                if ($type == 3 && $operate == 4) { //320->310
                    return $hundreds + 10;
                } elseif ($type == 4 && $operate == 3) {//320->732
                    return 700 + $hundred * 10 + 2;
                }
            }
            if ($remainder == 12) {
                if ($type == 2 && $operate == 4) {//312->310
                    return $hundreds + 10;
                } elseif ($type == 4 && $operate == 3) {//312->733
                    return 700 + $hundred * 10 + 3;
                }
            }
            if ($remainder == 21) {
                if ($type == 3 && $operate == 4) {//321->331
                    return $hundreds + 31;
                } elseif ($type == 4 && $operate == 3) {//321->735
                    return 700 + $hundred * 10 + 5;
                }
            }
            if ($remainder == 32) {//332->331
                if ($type == 2 && $operate == 4) {//332->734
                    return $hundreds + 31;
                } elseif ($type == 4 && $operate == 3) {//332->734
                    return 700 + $hundred * 10 + 4;
                }
            }
            if ($remainder == 30) {
                if ($type == 2 && $operate == 2) {//330->331
                    return $hundreds + 31;
                } elseif ($type == 3 && $operate == 4) {//330->300
                    return $hundreds;
                } elseif ($type == 3 && $operate == 2) {//330->310
                    return $hundreds + 10;
                }
            }
            if ($remainder == 40) {
                if ($type == 3 && $operate == 2) {//330->331
                    return $hundreds + 10;
                } elseif ($type == 1 && $operate == 4) {//330->300
                    return $hundreds;
                }
            }
        }
        return false;
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_status_record';
    }

    /**
     * @description 获取一条数据
     * @param array $where
     * @param array $fileds
     * @param array $orderBy
     */
    public static function getOne(array $where, array $fileds, array $orderBy)
    {
        $query = new Query();

        if (!empty($fileds)) {
            $query = $query->select($fileds);
        }
        if (!empty($where)) {
            $query = $query->where($where);
        }
        if (!empty($orderBy)) {
            $query = $query->orderby($orderBy);
        }

        if ($data = $query->from(get_called_class()::tableName())->one()) {
            $data['_id'] = (string)$data['_id'];
            return $data;
        }
        return false;
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
    public static function insertMongoOrderStatusRecord(int $uid, int $touid, string $orderId, int $status, string $reason, $money, $beforeStatus, $images = '', $isappeal = 0)
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
        $data['host'] = $_SERVER['HTTP_HOST']??'';
        $data['route'] = $_SERVER['REQUEST_URI']??'';
        $data['ip'] = $_SERVER['REMOTE_ADDR']??'';
        $data['create_time_zh'] = date('Y-m-d h:i:s');
        $data['is_pw_appeal'] = $isappeal;
//        $superId = self::getOne(['order_id' => $orderId], ['_id'], ['create_time' => SORT_ASC]);
//        $data['super_id'] = $superId ? $superId['_id'] : null;
        if (MongoService::insert(self::tableName(), $data)) {
            return true;
        } else {
            return false;
        }
    }

    public static function insertPrintScreen($order_id, $pic, $uid, $comment, $nickname = "")
    {
        $post = [];
        $post['order_id'] = $order_id;
        $post['comment'] = strval($comment);
        $post['path'] = $pic;
        $post['create_time'] = time();
        $post['is_delete'] = 0;
        $post['uid'] = intval($uid);
        $post['nickname'] = $nickname;
        if (MongoService::insert('pc_printscreen', $post)) {
            return true;
        }
        return false;
    }

    public static function giveUp($uid, $order_id)
    {
        $info = OrderAppealRecord::find()->where(['order_id' => $order_id])->one();
        if (!$info) {
            self::error(70005);
        }
        $orderinfo = ReleaseOrder::findOne(['order_id' => $order_id]);
        if ($info->from_uid == $uid) {
            $status = intval($orderinfo->status / 100).'00';
            MessageService::changeOrderStatePush($info->to_uid, $uid, Yii::$app->params['push_code']['cancel_appeal'], $order_id);
            self::insertMongoOrderStatusRecord($uid, $info->to_uid, $order_id, $status, '取消申请撤销', 0, $orderinfo->status );
            $orderinfo->status = $status;
            $orderinfo->save();
            $info->deleted_at = time();
        }else {
            $info->to_status = 2;
            $info->to_at = time();
            $info->status = 2;
            $status = intval($orderinfo->status / 100).'21';
            $orderinfo->status = $status;
            $orderinfo->save();
            self::insertMongoOrderStatusRecord($uid, $info->from_uid, $order_id, $status, '申请仲裁', 0, $orderinfo->status, '', 1);
        }
        if ($info->save()){
            return true;
        }

        self::error(70003);
    }
}