<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_course}}".
 *
 * @property int $id 课程ID
 * @property int $user_id 创建者ID
 * @property string $title 课程标题
 * @property string $images 课程封面
 * @property string $summary 课程摘要
 * @property string $description 课程解释
 * @property double $price 课程价格
 * @property int $type 课程类别
 * @property int $status 课程状态
 * @property int $add_time 创建时间
 * @property int $update_time 修改时间
 */
class XmBCourse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_course}}';
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
            [['user_id', 'title', 'add_time'], 'required'],
            [['user_id', 'type', 'status', 'add_time', 'update_time'], 'integer'],
            [['summary', 'description'], 'string'],
            [['price'], 'number'],
            [['title', 'images'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'images' => 'Images',
            'summary' => 'Summary',
            'description' => 'Description',
            'price' => 'Price',
            'type' => 'Type',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
