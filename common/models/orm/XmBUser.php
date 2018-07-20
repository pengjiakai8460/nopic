<?php

namespace common\models\orm;

use Yii;
use yii\web\IdentityInterface;
use SLApi\services\v1\RedisService;

/**
 * This is the model class for table "{{%xm_b_user}}".
 *
 * @property int $id
 * @property int $school_id 学校ID
 * @property string $account 唯一工号
 * @property string $password 密码
 * @property string $name 姓名
 * @property int $account_type 1:老师 2:学生
 * @property string $mobile 手机号码
 * @property string $headimg 头像
 * @property int $last_login 添加时间
 * @property int $status 用户状态1:启用 0:禁用(删除)
 * @property int $add_time 添加时间
 * @property int $update_time 修改时间
 * @property string $access_token
 * @property string $auth_key
 */
class XmBUser extends \yii\db\ActiveRecord implements IdentityInterface
{
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
    public static function tableName()
    {
        return '{{%xm_b_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['school_id', 'account', 'password', 'name', 'add_time'], 'required'],
            [['school_id', 'account_type', 'status', 'add_time', 'update_time', 'last_login'], 'integer'],
            [['account'], 'string', 'max' => 45],
            [['password'], 'string', 'max' => 80],
            [['name', 'auth_key', 'access_token'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 20],
            [['account'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'school_id' => 'School ID',
            'account' => 'Account',
            'password' => 'Password',
            'name' => 'Name',
            'account_type' => 'Account Type',
            'mobile' => 'Mobile',
            'headimg' => 'Headimg',
            'last_login' => 'Last Login',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
        ];
    }
    
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user =  @unserialize(RedisService::getRedis()->get($token. '-OBJ'));
        return $user;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
    
    public function generateAccessToken() {
        $this->access_token = Yii::$app->security->generateRandomString();   
        RedisService::getRedis()->set($this->access_token. '-OBJ', serialize($this), 7200);
        return $this->access_token;
    }
    
    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($account, $school_id, $account_type)
    {
        return static::findOne(['account' => $account, 'school_id' => $school_id, 'account_type'=> $account_type]);
    }
}
