<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_feedback".
 *
 * @property int $id 用户意见反馈表
 * @property int $uid 用户uid
 * @property string $title 反馈主题
 * @property string $type 类型
 * @property string $content 意见反馈内容
 * @property string $extra 额外扩充字段
 * @property int $status 1:正常 0:已删除
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次修改时间
 */
class XmCFeedback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'status', 'add_time', 'update_time'], 'integer'],
            [['title', 'type'], 'string', 'max' => 255],
            [['content', 'extra'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'title' => 'Title',
            'type' => 'Type',
            'content' => 'Content',
            'extra' => 'Extra',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
