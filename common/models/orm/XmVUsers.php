<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_users".
 *
 * @property int $id
 * @property string $user_name
 * @property string $password 用户密码
 * @property string $openId 用户openId
 * @property string $mobile 用户手机号，不得为空
 * @property string $avatar 用户图像
 * @property int $parentId 推荐人ID，为推广系统预留
 * @property string $cashs 用户余额，预留字段
 * @property string $token
 * @property int $createdTime 注册时间
 * @property string $createdIp 注册IP
 * @property int $loginTime 登陆时间
 * @property string $loginIp 登录IP
 * @property int $updatedTime 修改时间
 * @property string $updatedIp 修改地点
 * @property string $status normal默认正常登录，closed禁止登录
 * @property int $is_delete 1正常 2用户删除
 */
class XmVUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_users';
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
            [['user_name', 'password', 'mobile'], 'required'],
            [['parentId', 'createdTime', 'loginTime', 'updatedTime', 'is_delete'], 'integer'],
            [['cashs'], 'number'],
            [['status'], 'string'],
            [['user_name'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 64],
            [['openId'], 'string', 'max' => 100],
            [['mobile'], 'string', 'max' => 13],
            [['avatar'], 'string', 'max' => 150],
            [['token'], 'string', 'max' => 255],
            [['createdIp', 'loginIp', 'updatedIp'], 'string', 'max' => 16],
            [['user_name', 'openId', 'mobile'], 'unique', 'targetAttribute' => ['user_name', 'openId', 'mobile']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_name' => 'User Name',
            'password' => 'Password',
            'openId' => 'Open ID',
            'mobile' => 'Mobile',
            'avatar' => 'Avatar',
            'parentId' => 'Parent ID',
            'cashs' => 'Cashs',
            'token' => 'Token',
            'createdTime' => 'Created Time',
            'createdIp' => 'Created Ip',
            'loginTime' => 'Login Time',
            'loginIp' => 'Login Ip',
            'updatedTime' => 'Updated Time',
            'updatedIp' => 'Updated Ip',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
        ];
    }
}
