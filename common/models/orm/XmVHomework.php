<?php

namespace common\models\orm;

use Yii;

class XmVHomework extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_homework';
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
            [['summray', 'image'],'default','value' => ''],
           	[['title','url'], 'string'],
           	[['title','url'], 'required'],
            [['creator', 'updatedTime','createdTime','status','type'], 'integer'], 
        ];
    }

    /**
     * @inheritdoc
     */
    /*public function attributeLabels()
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
    }*/
}
