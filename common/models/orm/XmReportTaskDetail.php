<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_task_detail".
 *
 * @property int $id id
 * @property int $report_task_id report_task表id
 * @property int $user_id
 * @property int $e_id 每日任务id
 * @property int $q_id 问题id
 * @property string $answer 回答内容
 * @property int $status 状态 0 删除 1正常
 * @property int $add_time
 * @property int $update_time
 */
class XmReportTaskDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_report_task_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_task_id', 'user_id', 'e_id', 'q_id', 'status', 'add_time', 'update_time'], 'integer'],
            [['user_id', 'e_id', 'q_id', 'status', 'add_time', 'update_time'], 'required'],
            [['answer'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_task_id' => 'Report Task ID',
            'user_id' => 'User ID',
            'e_id' => 'E ID',
            'q_id' => 'Q ID',
            'answer' => 'Answer',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
