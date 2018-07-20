<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_section}}".
 *
 * @property int $id 课程小节ID
 * @property int $lesson_id 课程章节ID
 * @property string $title 课程小节ID
 * @property string $summary 课程小节摘要
 * @property string $image 课程小节图片
 * @property int $sort 排序
 * @property int $status 课程小节状态
 * @property int $add_time 创建时间
 * @property int $update_time 修改时间
 */
class XmBSection extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_section}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db6');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lesson_id', 'sort', 'status', 'add_time', 'update_time'], 'integer'],
            [['title', 'add_time'], 'required'],
            [['summary'], 'string'],
            [['title', 'image'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lesson_id' => 'Lesson ID',
            'title' => 'Title',
            'summary' => 'Summary',
            'image' => 'Image',
            'sort' => 'Sort',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
