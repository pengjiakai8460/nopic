<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_user_vip".
 *
 * @property int $id
 * @property int $user_id 用户uid
 * @property int $level 会员等级
 * @property int $begin_time 开始时间
 * @property int $end_time 结束时间
 * @property int $money 花费的金钱(单位:分)
 * @property int $status 1：正常  0: 已删除
 * @property int $add_time 创建时间
 * @property int $update_time 最近一次修改时间
 */
class XmCUserVip extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_user_vip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'begin_time', 'end_time', 'money'], 'required'],
            [['user_id', 'level', 'begin_time', 'end_time', 'money', 'status', 'add_time', 'update_time'], 'integer'],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'level' => 'Level',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'money' => 'Money',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
