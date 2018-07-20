<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course".
 *
 * @property int $id
 * @property string $title 课程标题
 * @property string $introduce 课程简介
 * @property string $goals 课程教学目标
 * @property string $hours 总课时，预留字段
 * @property string $picture 图片链接
 * @property string $price 课程价格
 * @property string $discount 折扣比例
 * @property int $categoryId 课程分类
 * @property int $creator 创建者
 * @property int $createdTime 创建时间
 * @property int $updatedTime 修改时间
 * @property int $is_delete 1正常 2删除
 * @property string $status unpublished未发布，published已发布，closed已下架
 */
class XmVCourseGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $dbname = substr(static::getDb()->dsn, strpos(static::getDb()->dsn, "dbname=") + 7);
        return $dbname . '.xm_v_course_goods';
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
            [['name', 'course_id', 'price', 'status'], 'required'],
            [['price'], 'number'],
            [['status', 'created_at', 'updated_at', 'creator_id', 'operator_id'], 'integer'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course Id',
            'name' => 'Name',
            'cover_image' => 'Cover Image',
            'price' => 'Price',
            'created_at' => 'Created Time',
            'updated_at' => 'Updated Time',
            'creator_id' => 'Creator',
            'operator_id' => 'Operator',
            'status' => 'Status',
        ];
    }

    public function getCourse()
    {
        return $this->hasOne(XmVCourse::class, ['id' => 'course_id'])->asArray();
    }

    public function getCreator()
    {
        return $this->hasOne(XmUser::class, ['id' => 'creator_id'])->asArray();
    }

    public function scenarios()
    {
        return [
            'save' => [
                'name', 'course_id', 'cover_image', 'price', 'available_from', 'available_to',
                'creator_id', 'created_at'
            ],
            'update' => [
                'id', 'name', 'course_id', 'cover_image', 'price', 'status', 'available_from', 'available_to',
                'operator_id', 'updated_at'
            ],
            'patch' => ['id', 'status', 'updated_at']
        ];
    }

}
