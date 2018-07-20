<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_users_course".
 *
 * @property int $id
 * @property int $usersId 用户id
 * @property int $courseId 课程id
 * @property int $sourceType 购买服务的来源:1,录播课平台购买;2,有赞平台
 * @property int $orderId 服务对应的订单id；0,表示非购买产生的服务待服务记录（()）
 * @property string $orderNumber 订单号
 * @property string $remark 服务备注
 * @property int $status 状态:1，正常；0，删除或已移除的服务记录
 * @property int $createTime 创建时间
 * @property int $updateTime 记录更新时间
 */
class XmVUsersCourse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_users_course';
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
            [['usersId', 'courseId', 'orderId', 'status', 'createTime', 'updateTime'], 'required'],
            [['usersId', 'courseId', 'sourceType', 'orderId', 'status', 'createTime', 'updateTime'], 'integer'],
            [['orderNumber'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'usersId' => 'Users ID',
            'courseId' => 'Course ID',
            'sourceType' => 'Source Type',
            'orderId' => 'Order ID',
            'orderNumber' => 'Order Number',
            'remark' => 'Remark',
            'status' => 'Status',
            'createTime' => 'Create Time',
            'updateTime' => 'Update Time',
        ];
    }
}
