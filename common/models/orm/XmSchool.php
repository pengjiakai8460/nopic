<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_school".
 *
 * @property int $id 主键自增
 * @property string $name 学校名称
 * @property string $address 学校地址
 * @property int $length 学制
 * @property int $type 类型：1公办2民办
 * @property string $remark 备注
 * @property int $status 状态：0禁用1正常-1删除，默认1
 * @property int $adder_id 录入人员
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 */
class XmSchool extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_school';
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
            [['name', 'length', 'type', 'adder_id', 'add_time', 'update_time'], 'required'],
            [['length', 'type', 'status', 'adder_id', 'add_time', 'update_time'], 'integer'],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 200],
            [['address'], 'string', 'max' => 2000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'address' => 'Address',
            'length' => 'Length',
            'type' => 'Type',
            'remark' => 'Remark',
            'status' => 'Status',
            'adder_id' => 'Adder ID',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
