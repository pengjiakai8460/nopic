<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "xm_c_exam".
 *
 * @property int $id 主键自增
 * @property string $title 试卷标题
 * @property int $type 类型：1：真题，2：模拟, 3:每日任务
 * @property int $all_score 总分数
 * @property int $adder_id 添加人ID
 * @property int $all_times 是否有时间限制
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次修改时间
 * @property int $status 1:正常  0:删除
 */
class XmCExam extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_exam';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'all_score', 'adder_id', 'all_times', 'add_time', 'update_time', 'status'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'type' => 'Type',
            'all_score' => 'All Score',
            'adder_id' => 'Adder ID',
            'all_times' => 'All Times',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
