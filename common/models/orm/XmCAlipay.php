<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_alipay".
 *
 * @property int $id 主键自增
 * @property int $users_id 用户ID
 * @property int $pay_type 订单类型，1为支付宝，2为微信
 * @property int $unit_type 单位类型.   1:月  2:年
 * @property int $num 表示单位数量 几月或者几年
 * @property string $out_trade_no 订单唯一标识
 * @property int $money 支付金额，单位：分
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 * @property int $used_status 0:表示未使用  1:表示已使用
 * @property int $status 状态：-1、0、1，-1：删除，0：未处理，1：已处理
 * @property string $trade_status 阿里云返回的状态
 * @property string $log_request 日志的名称在OSS的地址
 * @property string $log_response 日志的名称在OSS的地址
 */
class XmCAlipay extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_alipay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['users_id', 'pay_type', 'out_trade_no', 'money', 'add_time', 'update_time', 'status'], 'required'],
            [['users_id', 'pay_type', 'unit_type', 'num', 'money', 'add_time', 'update_time', 'used_status', 'status'], 'integer'],
            [['out_trade_no'], 'string', 'max' => 200],
            [['trade_status'], 'string', 'max' => 80],
            [['log_request', 'log_response'], 'string', 'max' => 800],
            [['out_trade_no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'users_id' => 'Users ID',
            'pay_type' => 'Pay Type',
            'unit_type' => 'Unit Type',
            'num' => 'Num',
            'out_trade_no' => 'Out Trade No',
            'money' => 'Money',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'used_status' => 'Used Status',
            'status' => 'Status',
            'trade_status' => 'Trade Status',
            'log_request' => 'Log Request',
            'log_response' => 'Log Response',
        ];
    }
}
