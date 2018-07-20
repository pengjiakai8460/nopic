<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_logs".
 *
 * @property int $id
 * @property string $message 日志内容
 * @property int $createdTime 创建时间
 */
class XmVLogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_logs';
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
            [['message', 'createdTime'], 'required'],
            [['message'], 'string'],
            [['createdTime'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message' => 'Message',
            'createdTime' => 'Created Time',
        ];
    }
}
