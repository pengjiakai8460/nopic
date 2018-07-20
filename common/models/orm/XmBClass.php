<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_class}}".
 *
 * @property int $id 班级ID
 * @property int $school_id 学校ID
 * @property string $name 班级名称
 * @property string $number 班级编号
 * @property int $type 课程类型
 * @property string $comments 班级描述
 * @property int $course_id 课程编号
 * @property int $lesson_id 课程进度
 * @property int $status 班级状态
 * @property int $add_time 添加时间
 * @property int $update_time 修改时间
 */
class XmBClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_class}}';
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
            [['school_id', 'name', 'add_time'], 'required'],
            [['school_id', 'type', 'course_id', 'lesson_id', 'status', 'add_time', 'update_time'], 'integer'],
            [['comments'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['number'], 'string', 'max' => 50],
            [['number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'school_id' => 'School ID',
            'name' => 'Name',
            'number' => 'Number',
            'type' => 'Type',
            'comments' => 'Comments',
            'course_id' => 'Course ID',
            'lesson_id' => 'Lesson ID',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
