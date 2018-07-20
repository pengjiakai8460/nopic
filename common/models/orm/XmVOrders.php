<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_orders".
 *
 * @property int $id
 * @property string $orderSn 订单号
 * @property int $courseId 课程Id 
 * @property string $title 订单描述
 * @property int $price 订单价格，单位分
 * @property string $discount 折扣比例
 * @property int $userId 用户ID
 * @property string $status draft未支付，paid已支付，cancel取消
 * @property int $createdTime 订单创建时间
 * @property int $paidTime 支付时间
 * @property string $payment 支付方式，alipay支付宝，wechat微信
 * @property string $note 订单备注
 * @property int $is_delete 1正常 2删除
 * @property int $chn 产生订单的渠道来源，非支付来源
 * @property int $classesId 班级id;0表示未分班
 */
class XmVOrders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_orders';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db4');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderSn', 'courseId', 'title', 'price', 'userId'], 'required'],
            [['courseId', 'price', 'userId', 'createdTime', 'paidTime', 'is_delete', 'chn', 'classesId'], 'integer'],
            [['discount'], 'number'],
            [['status', 'payment'], 'string'],
            [['orderSn'], 'string', 'max' => 32],
            [['title', 'note'], 'string', 'max' => 255],
            [['orderSn'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orderSn' => 'Order Sn',
            'courseId' => 'Course ID',
            'title' => 'Title',
            'price' => 'Price',
            'discount' => 'Discount',
            'userId' => 'User ID',
            'status' => 'Status',
            'createdTime' => 'Created Time',
            'paidTime' => 'Paid Time',
            'payment' => 'Payment',
            'note' => 'Note',
            'is_delete' => 'Is Delete',
            'chn' => 'Chn',
            'classesId' => 'Classes ID',
        ];
    }
}
