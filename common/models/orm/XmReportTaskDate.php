<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_task".
 *
 * @property int $id
 * @property int $user_id
 * @property int $e_id 每日任务id
 * @property int $add_time 添加时间
 * @property int $update_time 创建时间
 */
class XmReportTaskDate extends \yii\db\ActiveRecord
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
            [['id', 'user_id', 'e_id', 'add_time', 'update_time'], 'required'],
            [['id', 'user_id', 'e_id', 'add_time', 'update_time'], 'integer'],
            [['id'], 'unique'],
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
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
