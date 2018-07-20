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
class XmVCourse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $dbname = substr(static::getDb()->dsn, strpos(static::getDb()->dsn, "dbname=")+7);
        return $dbname.'.xm_v_course';
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
            [['title'], 'required'],
            [['status'], 'string'],
            //[['summray'],'required'],
            [['hours', 'price', 'discount'], 'number'],
            [['categoryId', 'creator', 'createdTime', 'updatedTime', 'is_delete'], 'integer'],
            ['summray', 'default', 'value' => null],
            [['title'], 'string', 'max' => 100],
            [['goals'], 'string', 'max' => 255],
            [['picture'], 'string', 'max' => 150],
            [['title'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'goals' => 'Goals',
            'summray' => 'Summray',
            'hours' => 'Hours',
            'picture' => 'Picture',
            'price' => 'Price',
            'discount' => 'Discount',
            'categoryId' => 'Category ID',
            'creator' => 'Creator',
            'createdTime' => 'Created Time',
            'updatedTime' => 'Updated Time',
            'is_delete' => 'Is Delete',
            'status' => 'Status',
        ];
    }
}
