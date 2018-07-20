<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_classes_lesson".
 *
 * @property int $id
 * @property int $classesId 班级id
 * @property int $courseId 课程id
 * @property int $lessonId 课程下的课的id
 * @property int $creator 开课操作人的id
 * @property int $createTime 开课时间
 * @property int $updateTime 记录最后变更时间
 * @property int $status 记录状态：1，正常，0，无效或者已删除
 */
class XmVClassesLesson extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_classes_lesson';
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
            [['classesId', 'courseId', 'lessonId', 'creator', 'createTime', 'updateTime', 'status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'classesId' => 'Classes ID',
            'courseId' => 'Course ID',
            'lessonId' => 'Lesson ID',
            'creator' => 'Creator',
            'createTime' => 'Create Time',
            'updateTime' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
