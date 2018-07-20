<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_report_exam_question".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $report_id 答案ID
 * @property int $exam_id 试卷ID
 * @property int $t_id 问题组ID
 * @property int $q_id 问题ID
 * @property int $is_right 回答题目 是否正确 0错误 1正确
 * @property string $answer 答案
 * @property string $answer_note 答案的注释：用于指明每个答案是否正确
 * @property int $times 总共耗时
 * @property int $right_num 答对的数量
 * @property double $score 获得的分数
 * @property int $error_type 错误类型
 * @property string $remark 学生备注
 * @property int $is_accept 0:未提交  1已提交
 * @property int $status 1:正常  0:删除
 * @property int $add_time 创建时间
 * @property int $update_time 最近一次更新时间
 */
class XmReportExamQuestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_report_exam_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'exam_id'], 'required'],
            [['user_id', 'report_id', 'exam_id', 't_id', 'q_id', 'is_right', 'times', 'right_num', 'error_type', 'is_accept', 'status', 'add_time', 'update_time'], 'integer'],
            [['answer', 'answer_note'], 'string'],
            [['score'], 'number'],
            [['remark'], 'string', 'max' => 512],
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
            'report_id' => 'Report ID',
            'exam_id' => 'Exam ID',
            't_id' => 'T ID',
            'q_id' => 'Q ID',
            'is_right' => 'Is Right',
            'answer' => 'Answer',
            'answer_note' => 'Answer Note',
            'times' => 'Times',
            'right_num' => 'Right Num',
            'score' => 'Score',
            'error_type' => 'Error Type',
            'remark' => 'Remark',
            'is_accept' => 'Is Accept',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
