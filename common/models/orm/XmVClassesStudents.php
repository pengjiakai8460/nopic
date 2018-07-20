<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_classes_students".
 *
 * @property int $id
 * @property int $classId 班级ID
 * @property int $sid 学员ID
 * @property int $createdTime 创建时间
 */
class XmVClassesStudents extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_classes_students';
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
            [['classId', 'sid', 'createdTime'], 'required'],
            [['classId', 'sid', 'createdTime'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'classId' => 'Class ID',
            'sid' => 'Sid',
            'createdTime' => 'Created Time',
        ];
    }
}
