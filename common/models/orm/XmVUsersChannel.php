<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_users_channel".
 *
 * @property int $id
 * @property int $users_id 用户id
 * @property int $channel 对应的渠道码
 * @property int $create_time 添加时间
 * @property int $update_time 更新时间
 */
class XmVUsersChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_users_channel';
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
            [['users_id', 'channel', 'create_time', 'update_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'users_id' => 'Users ID',
            'channel' => 'Channel',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
