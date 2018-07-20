<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_classes".
 *
 * @property int $id
 * @property string $name 班级名称
 * @property int $courseId 课程ID
 * @property int $creator 创建者
 * @property string $status draft未开始,ongoing进行中，close已关闭
 * @property int $openTime 开班时间
 * @property int $closeTime 结束时间
 * @property int $createdTime 班级创建时间
 * @property int $is_delete 1正常 2删除
 */
class XmV extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_classes';
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
            [['name', 'courseId'], 'required'],
            [['courseId', 'creator', 'openTime', 'closeTime', 'createdTime', 'is_delete'], 'integer'],
            [['status'], 'string'],
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
            'name' => 'Name',
            'courseId' => 'Course ID',
            'creator' => 'Creator',
            'status' => 'Status',
            'openTime' => 'Open Time',
            'closeTime' => 'Close Time',
            'createdTime' => 'Created Time',
            'is_delete' => 'Is Delete',
        ];
    }
}
