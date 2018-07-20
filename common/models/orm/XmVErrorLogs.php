<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_error_logs".
 *
 * @property int $id
 * @property string $type 日志类型
 * @property string $code 状态码
 * @property string $info 日志详细信息
 * @property string $url 用户操作页面URL
 * @property int $user_id 用户id，该字段可不存
 * @property string $ip 用户操作ip
 * @property int $created_at 创建时间，格式为时间戳
 */
class XmVErrorLogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_error_logs';
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
            [['type', 'code', 'info', 'url', 'ip', 'created_at'], 'required'],
            [['info'], 'string'],
            [['user_id', 'created_at'], 'integer'],
            [['type'], 'string', 'max' => 60],
            [['code'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 200],
            [['ip'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'code' => 'Code',
            'info' => 'Info',
            'url' => 'Url',
            'user_id' => 'User ID',
            'ip' => 'Ip',
            'created_at' => 'Created At',
        ];
    }
}
