<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course_homework".
 *
 * @property int $id
 * @property int $chapterId
 * @property int $work_id 关联xm_v_homework 主键ID
 * @property int $createdTime
 */
class XmVCourseHomework extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_course_homework';
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
            [['chapterId', 'homeworkId'], 'required'],
            [['chapterId', 'homeworkId', 'createdTime'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chapterId' => 'Chapter ID',
            'homeworkId' => 'homeworkId',
            'createdTime' => 'Created Time',
        ];
    }
}

