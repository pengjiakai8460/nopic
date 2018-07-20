<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_question".
 *
 * @property int $id
 * @property string $qname 题目昵称
 * @property string $title 题目标题
 * @property int $type 题目类型，1：选择题2：问题求解，3：阅读程序写结果 4：完善程序
 * @property string $content 题目内容 json格式 c:内容,n:前缀,is_r:是否是正确内容 s:分数 
 * @property int $complexity 难度 1代表1颗星2代表2颗星以此类推
 * @property int $from_type 来源类型，1：真题，2：模拟
 * @property int $from_yearannual 来源的年份
 * @property int $answer_count 答案个数
 * @property string $explain 解析
 * @property double $score 分数
 * @property int $adder_id 作者ID
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 * @property int $status 1:正常  0:删除
 */
class XmCQuestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'explain'], 'string'],
            [['type', 'complexity', 'from_type', 'from_yearannual', 'answer_count', 'adder_id', 'add_time', 'update_time', 'status'], 'integer'],
            [['score'], 'number'],
            [['qname'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qname' => 'Qname',
            'title' => 'Title',
            'type' => 'Type',
            'content' => 'Content',
            'complexity' => 'Complexity',
            'from_type' => 'From Type',
            'from_yearannual' => 'From Yearannual',
            'answer_count' => 'Answer Count',
            'explain' => 'Explain',
            'score' => 'Score',
            'adder_id' => 'Adder ID',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
