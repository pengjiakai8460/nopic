<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_classes_signin".
 *
 * @property int $id 学员签到表
 * @property int $classId 班级ID
 * @property int $courseId 课程ID
 * @property int $lessonId lesson主键ID
 * @property int $chapterId 章节ID
 * @property int $sid 学员ID
 * @property int $startTime 开始时间
 * @property int $endTime 结束时间
 * @property int $createdTime 系统录入时间
 */
class XmVClassesSignin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_classes_signin';
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
            [['classId', 'courseId', 'lessonId', 'chapterId', 'sid'], 'required'],
            [['classId', 'courseId', 'lessonId', 'chapterId', 'sid', 'startTime', 'endTime', 'createdTime'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'classId' => 'Class ID',
            'courseId' => 'Course ID',
            'lessonId' => 'Lesson ID',
            'chapterId' => 'Chapter ID',
            'sid' => 'Sid',
            'startTime' => 'Start Time',
            'endTime' => 'End Time',
            'createdTime' => 'Created Time',
        ];
    }
}
