<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_testlets".
 *
 * @property int $id
 * @property string $title 题目组描述
 * @property string $subtitle 副标题
 * @property int $day 如果是任务时则有描述
 * @property int $type 0:试卷的题目组  1:任务
 * @property int $status 1:正常  0:删除
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次修改时间
 */
class XmCTestlets extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_testlets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['day', 'type', 'status', 'add_time', 'update_time'], 'integer'],
            [['title', 'subtitle'], 'string', 'max' => 1024],
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
            'subtitle' => 'Subtitle',
            'day' => 'Day',
            'type' => 'Type',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
