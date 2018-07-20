<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course_category".
 *
 * @property int $id
 * @property string $name 课程分类名称
 * @property int $parentId 上级ID
 * @property int $createdTime 创建时间
 */
class XmVCourseCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $dbname = substr(static::getDb()->dsn, strpos(static::getDb()->dsn, "dbname=")+7);
        return $dbname.'.xm_v_course_category';
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
            [['name'], 'required'],
            [['parentId', 'createdTime'], 'integer'],
            [['name'], 'string', 'max' => 50],
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
            'parentId' => 'Parent ID',
            'createdTime' => 'Created Time',
        ];
    }
}
