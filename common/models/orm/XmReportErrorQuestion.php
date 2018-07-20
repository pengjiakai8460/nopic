<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_error_question".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $q_id 问题id
 * @property int $error_count 错误次数
 * @property int $error_type 错误原因 1 计算错误 2 常识记忆错误 3算法错误 4 开发性题型错误 5其他
 * @property string $remark 备注
 * @property int $add_time 创建时间
 * @property int $update_time 修改时间
 */
class XmReportErrorQuestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_report_error_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'q_id', 'error_count', 'error_type', 'add_time', 'update_time'], 'integer'],
            [['error_count', 'error_type', 'add_time', 'update_time'], 'required'],
            [['remark'], 'string', 'max' => 1024],
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
            'q_id' => 'Q ID',
            'error_count' => 'Error Count',
            'error_type' => 'Error Type',
            'remark' => 'Remark',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
