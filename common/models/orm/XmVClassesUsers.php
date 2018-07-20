<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_classes_users".
 *
 * @property int $id
 * @property int $classId 班级ID
 * @property int $courseId 课程id
 * @property int $usersId 学员ID
 * @property int $creator 记录操作人id
 * @property int $createdTime 创建时间
 * @property int $updateTime 更新时间
 * @property int $status 1，正常；
 */
class XmVClassesUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_classes_users';
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
            [['classId', 'usersId', 'createdTime', 'updateTime'], 'required'],
            [['classId', 'courseId', 'usersId', 'creator', 'createdTime', 'updateTime', 'status'], 'integer'],
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
            'usersId' => 'Users ID',
            'creator' => 'Creator',
            'createdTime' => 'Created Time',
            'updateTime' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
