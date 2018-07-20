<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_homework}}".
 *
 * @property int $id 作业
 * @property int $uid 学生ID
 * @property int $resourse_id 资源ID
 * @property int $section_id 课程小节ID
 * @property int $is_finish 是否完成 0：未完成 1：完成
 * @property int $status 课程状态
 * @property int $is_excellent 是否优秀 0：未批改 1：优秀  2：不优秀
 * @property string $comment 评语
 * @property int $add_time 提交时间
 * @property int $update_time 修改时间
 */
class XmBHomework extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_homework}}';
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
            [['id', 'uid', 'resourse_id', 'section_id', 'add_time'], 'required'],
            [['id', 'uid', 'resourse_id', 'section_id', 'is_finish', 'status', 'is_excellent', 'add_time', 'update_time'], 'integer'],
            [['comment'], 'string'],
            [['id'], 'unique'],
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
            'resourse_id' => 'Resourse ID',
            'section_id' => 'Section ID',
            'is_finish' => 'Is Finish',
            'status' => 'Status',
            'is_excellent' => 'Is Excellent',
            'comment' => 'Comment',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
