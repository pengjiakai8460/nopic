<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "admin".
 *
 * @property integer $id
 * @property string $username
 * @property string $phone
 * @property string $nickname
 * @property string $realname
 * @property integer $sex
 * @property string $idcard
 * @property integer $status
 * @property string $auth_key
 * @property integer $last_login_time
 * @property integer $login_count
 * @property string $avatar
 * @property string $password
 * @property string $password_hash
 * @property string $password_reset_token
 * @property integer $updated_at
 * @property integer $created_at
 */
class Admin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'created_at'], 'required'],
            [['sex', 'status', 'last_login_time', 'login_count', 'updated_at', 'created_at'], 'integer'],
            [['username', 'nickname', 'realname', 'idcard', 'avatar', 'password'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 16],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'phone' => 'Phone',
            'nickname' => 'Nickname',
            'realname' => 'Realname',
            'sex' => 'Sex',
            'idcard' => 'Idcard',
            'status' => 'Status',
            'auth_key' => 'Auth Key',
            'last_login_time' => 'Last Login Time',
            'login_count' => 'Login Count',
            'avatar' => 'Avatar',
            'password' => 'Password',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }
}
