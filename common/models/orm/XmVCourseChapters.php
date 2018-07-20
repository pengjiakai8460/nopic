<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course_chapters".
 *
 * @property int $id
 * @property int $lessonId 课程ID
 * @property string $title 课程下的章节标题
 * @property int $seq 排序字段，预留
 * @property int $startTime 开始时间
 * @property int $endTime 结束时间
 * @property int $createdTime 创建时间
 * @property int $is_delete 1正常 2删除
 * @property string $status draft未发布 published 已发布，close已关闭
 * @property int $creator 创建者ID
 */
class XmVCourseChapters extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_course_lesson_chapters';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db4');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lessonId', 'title'], 'required'],
            [['lessonId', 'seq', 'startTime', 'endTime', 'createdTime', 'is_delete', 'creator',], 'integer'],
            ['summray', 'default', 'value' => null],
            [['status'], 'string'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lessonId' => 'Lesson ID',
            'title' => 'Title',
            'summray' => 'summray',
            'seq' => 'Seq',
            'startTime' => 'Start Time',
            'endTime' => 'End Time',
            'createdTime' => 'Created Time',
            'is_delete' => 'Is Delete',
            'status' => 'Status',
            'creator' => 'Creator',
        ];
    }
}
