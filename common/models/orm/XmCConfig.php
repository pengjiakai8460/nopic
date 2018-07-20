<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_config".
 *
 * @property int $id
 * @property string $key key值
 * @property string $value value值
 * @property string $remark 注释
 * @property int $status 1:正常  0:已删除
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次修改时间
 */
class XmCConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'add_time', 'update_time'], 'integer'],
            [['key', 'remark'], 'string', 'max' => 255],
            [['value'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'remark' => 'Remark',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
