<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_user_data".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $correct_rate 正确率
 * @property int $calcu_date 统计的日期
 * @property int $status 状态 1有效 0 无效
 * @property int $add_time 创建时间
 * @property int $update_time 修改时间
 */
class XmReportUserData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_report_user_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'correct_rate', 'calcu_date', 'add_time', 'update_time'], 'integer'],
            [['status'], 'string', 'max' => 1],
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
            'correct_rate' => 'Correct Rate',
            'calcu_date' => 'Calcu Date',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
