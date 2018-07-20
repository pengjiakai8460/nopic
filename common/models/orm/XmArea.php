<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_area".
 *
 * @property int $id
 * @property int $actid
 * @property string $name
 * @property string $code 区号
 * @property int $citycode 百度的citycode
 * @property int $status 状态：0为默认1为开设0为没有开设
 * @property int $sort 排序字段
 */
class XmArea extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_area';
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
            [['actid', 'name', 'code', 'citycode'], 'required'],
            [['actid', 'citycode', 'status', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['code'], 'string', 'max' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'actid' => 'Actid',
            'name' => 'Name',
            'code' => 'Code',
            'citycode' => 'Citycode',
            'status' => 'Status',
            'sort' => 'Sort',
        ];
    }
}
