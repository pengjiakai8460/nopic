<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_sendphonecode".
 *
 * @property int $id 主键自增
 * @property string $phone 手机号
 * @property string $code 验证码
 * @property int $add_time 添加时间
 */
class XmSendphonecode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_sendphonecode';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'code', 'add_time'], 'required'],
            [['add_time'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['code'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'code' => 'Code',
            'add_time' => 'Add Time',
        ];
    }
}
