<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_user_class}}".
 *
 * @property int $id 关系表ID
 * @property int $uid 用户ID
 * @property int $class_id 班级ID
 */
class XmBUserClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_user_class}}';
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
            [['uid', 'class_id'], 'required'],
            [['uid', 'class_id'], 'integer'],
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
            'class_id' => 'Class ID',
        ];
    }
}
