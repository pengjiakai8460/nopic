<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_user_course_goods".
 *
 * @property int $id
 * @property int $course_goods_id 课程商品id
 * @property int $order_id 订单id
 * @property int $user_id 用户id
 * @property int $status 状态：0,未分班;1,已分班;
 * @property int $class_id 班级id
 * @property int $created_at 记录生成时间
 * @property int $updated_at 记录最后变更时间
 * @property int $is_deleted 是否删除记录
 */
class XmVUserCourseGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_user_course_goods';
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
            [['course_goods_id', 'order_id', 'user_id'], 'required'],
            [['course_goods_id', 'order_id', 'status', 'class_id', 'created_at', 'updated_at', 'is_deleted'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User id',
            'course_goods_id' => 'Course Goods ID',
            'order_id' => 'Order ID',
            'status' => 'Status',
            'class_id' => 'Class ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_deleted' => 'Is Deleted',
        ];
    }
}
