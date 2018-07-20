<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_exam".
 *
 * @property int $id 主键自增
 * @property int $user_id 用户id
 * @property int $exam_id 套题id
 * @property double $score 分数
 * @property int $correct_rate 正确数量
 * @property int $times 总共耗时
 * @property int $complete_state 0:未完成  100:已完成
 * @property int $add_time 创建时间
 * @property int $update_time 更新时间
 * @property int $status 1:正常  0:已删除
 * @property int $is_accept 1:已提交 0:未提交
 */
class XmReportExam extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_report_exam';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'exam_id', 'score', 'correct_rate', 'times', 'add_time', 'update_time'], 'required'],
            [['user_id', 'exam_id', 'correct_rate', 'times', 'complete_state', 'add_time', 'update_time', 'status', 'is_accept'], 'integer'],
            [['score'], 'number'],
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
            'exam_id' => 'Exam ID',
            'score' => 'Score',
            'correct_rate' => 'Correct Rate',
            'times' => 'Times',
            'complete_state' => 'Complete State',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'is_accept' => 'Is Accept',
        ];
    }
}
