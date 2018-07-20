<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_task".
 *
 * @property int $id
 * @property int $user_id
 * @property int $e_id 每日任务id
 * @property int $is_complete 是否完成 0 未完成 1完成
 * @property int $add_time 添加时间
 * @property int $update_time 创建时间
 */
class XmReportTask extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_report_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'e_id', 'add_time', 'update_time'], 'required'],
            [['user_id', 'e_id', 'is_complete', 'add_time', 'update_time'], 'integer'],
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
            'e_id' => 'E ID',
            'is_complete' => 'Is Complete',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
