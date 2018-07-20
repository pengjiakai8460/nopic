<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_user_detail".
 *
 * @property int $user_id 人员id
 * @property int $is_delete 是否删除
 * @property string $avator 图像
 * @property string $account_balanc 账户余额
 * @property int $online 是否 在线
 * @property int $last_login_time 最后登录时间
 * @property int $login_day 连续登录天数 
 * @property int $all_day 登录总天数
 * @property int $grade_id 等级id
 * @property int $last_task_time 最后做每日任务时间
 * @property int $task_day 连续完成每日任务天数
 * @property string $autograph 签名
 * @property int $status
 * @property int $add_time 创建时间
 * @property int $update_time 修改时间
 */
class XmCUserDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_user_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'is_delete', 'account_balanc', 'add_time', 'update_time'], 'required'],
            [['user_id', 'is_delete', 'online', 'last_login_time', 'login_day', 'all_day', 'grade_id', 'last_task_time', 'task_day', 'status', 'add_time', 'update_time'], 'integer'],
            [['account_balanc'], 'number'],
            [['avator', 'autograph'], 'string', 'max' => 255],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'is_delete' => 'Is Delete',
            'avator' => 'Avator',
            'account_balanc' => 'Account Balanc',
            'online' => 'Online',
            'last_login_time' => 'Last Login Time',
            'login_day' => 'Login Day',
            'all_day' => 'All Day',
            'grade_id' => 'Grade ID',
            'last_task_time' => 'Last Task Time',
            'task_day' => 'Task Day',
            'autograph' => 'Autograph',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
