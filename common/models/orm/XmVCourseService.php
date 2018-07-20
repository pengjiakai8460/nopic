<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course_service".
 *
 * @property int $id
 * @property int $courseId  课程标题
 * @property string $title 课程服务的标题
 * @property string $img 图片地址
 * @property int $price 服务对应的价格(默认为对应课程的初始价格)
 * @property int $type 类型：1，普通课程服务，2：独立的课程服务
 * @property int $status 状态:1, 正常; -1, 删除
 * @property int $creator 创建人id，对应xm_user表
 * @property int $startTime 服务开始时间
 * @property int $endTime 服务结束时间
 * @property int $createTime 创建时间
 * @property int $updateTime 记录变更时间
 */
class XmVCourseService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $dbname = substr(static::getDb()->dsn, strpos(static::getDb()->dsn, "dbname=")+7);
        return $dbname.'.xm_v_course_service';
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
            [['courseId', 'img', 'price', 'creator', 'startTime', 'endTime', 'createTime', 'updateTime'], 'required'],
            [['courseId', 'price', 'type', 'status', 'creator', 'startTime', 'endTime', 'createTime', 'updateTime'], 'integer'],
            [['title', 'img'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'courseId' => 'Course ID',
            'title' => 'Title',
            'img' => 'Img',
            'price' => 'Price',
            'type' => 'Type',
            'status' => 'Status',
            'creator' => 'Creator',
            'startTime' => 'Start Time',
            'endTime' => 'End Time',
            'createTime' => 'Create Time',
            'updateTime' => 'Update Time',
        ];
    }
}
