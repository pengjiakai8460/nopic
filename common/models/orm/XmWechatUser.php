<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_wechat_user".
 *
 * @property int $id
 * @property string $wechat_openid 用户微信号
 * @property string $nickname 用户名
 * @property string $headimgurl 头像
 * @property int $sex 性别：1男2女
 * @property string $city 城市
 * @property string $province 省份
 * @property int $wechat_type 微信公众号类型（1：小码王  2：小码王教学服务）
 */
class XmWechatUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_wechat_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname'], 'required'],
            [['wechat_openid'], 'string', 'max' => 100],
            [['nickname'], 'string', 'max' => 40],
            [['headimgurl'], 'string', 'max' => 150],
            [['sex'], 'string', 'max' => 3],
            [['city', 'province'], 'string', 'max' => 20],
            //[['wechat_type'], 'string', 'max' => 2],
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
            'wechat_openid' => 'Wechat Openid',
            'nickname' => 'Nickname',
            'headimgurl' => 'Headimgurl',
            'sex' => 'Sex',
            'city' => 'City',
            'province' => 'Province',
            'wechat_type' => 'Wechat Type',
        ];
    }
}
