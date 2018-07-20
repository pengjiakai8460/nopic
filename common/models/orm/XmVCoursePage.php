<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course_page".
 *
 * @property int $id
 * @property int $course_id 课程id
 * @property string $title 标题
 * @property string $summary 简介
 * @property string $head_img 头图
 * @property string $bottom_img 底图
 * @property string $cover_img 封面图
 * @property string $detail_img 详情图（多张）
 * @property int $status 状态: 1,正常使用; 0,隐藏;
 * @property int $creator 添加人ID
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $is_deleted 是否删除：0, 不删除; 1,删除
 */
class XmVCoursePage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_course_page';
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
            [['course_id', 'created_at', 'updated_at'], 'required'],
            [['course_id', 'creator', 'status', 'created_at', 'updated_at', 'is_deleted'], 'integer'],
            [['summary', 'head_img', 'bottom_img', 'cover_img', 'detail_img'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course ID',
            'title' => 'Title',
            'summary' => 'Summary',
            'head_img' => 'Head Img',
            'bottom_img' => 'Bottom Img',
            'cover_img' => 'Cover Img',
            'detail_img' => 'Detail Img',
            'status' => 'Status',
            'creator' => 'Creator',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_deleted' => 'Is Deleted',
        ];
    }
}
