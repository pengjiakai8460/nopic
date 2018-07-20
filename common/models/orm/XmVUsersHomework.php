<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_users_homework".
 *
 * @property int $id
 * @property int $homeworkId 作业ID
 * @property int $composeId 学生作品id
 * @property int $userId 学员id
 * @property int $classId 班级id
 * @property int $chapterId 小节id
 * @property string $url 学生作业保存地址
 * @property int $is_finished 1未做完 2做完了
 * @property int $type 1代表学生作业
 * @property int $status 1未修改2已修改
 * @property int $is_excellent 1优秀 2不优秀
 * @property string $comment 点评
 * @property int $score 评分 12345星
 * @property int $is_delete 1正常 2删除
 * @property int $createdTime
 * @property int $updatedTime
 */
class XmVUsersHomework extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_users_homework';
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
            [['homeworkId', 'composeId', 'userId', 'classId', 'chapterId', 'updatedTime'], 'required'],
            [['homeworkId', 'composeId', 'userId', 'classId', 'chapterId', 'is_finished', 'type', 'status', 'is_excellent', 'score', 'is_delete', 'createdTime', 'updatedTime'], 'integer'],
            [['comment'], 'string'],
            [['url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'homeworkId' => 'Homework ID',
            'composeId' => 'Compose ID',
            'userId' => 'User ID',
            'classId' => 'Class ID',
            'chapterId' => 'Chapter ID',
            'url' => 'Url',
            'is_finished' => 'Is Finished',
            'type' => 'Type',
            'status' => 'Status',
            'is_excellent' => 'Is Excellent',
            'comment' => 'Comment',
            'score' => 'Score',
            'is_delete' => 'Is Delete',
            'createdTime' => 'Created Time',
            'updatedTime' => 'Updated Time',
        ];
    }
}
