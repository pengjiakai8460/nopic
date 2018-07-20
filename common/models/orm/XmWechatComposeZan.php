<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_wechat_compose_zan".
 *
 * @property int $id 主键自增
 * @property string $open_id 微信用户open_id
 * @property int $compose_id 作品ID
 * @property int $status 点赞状态 0-取消赞 1-有效赞
 * @property int $add_time 投稿时间
 */
class XmWechatComposeZan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_wechat_compose_zan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['compose_id', 'add_time'], 'integer'],
            [['open_id'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 3],
        ];
    }

    public static function getDb()
    {
        return Yii::$app->get('db2');
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'open_id' => 'Open ID',
            'compose_id' => 'Compose ID',
            'status' => 'Status',
            'add_time' => 'Add Time',
        ];
    }
}
