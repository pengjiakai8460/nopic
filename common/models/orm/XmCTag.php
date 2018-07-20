<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_tag".
 *
 * @property int $id 主键自增
 * @property int $pid 父id
 * @property int $top 顶级id
 * @property string $name 名称
 * @property string $remark 备注
 * @property int $adder_id 作者ID
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次更新时间
 * @property int $status 1:正常  0:已删除
 */
class XmCTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'top', 'adder_id', 'add_time', 'update_time', 'status'], 'integer'],
            [['name', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'Pid',
            'top' => 'Top',
            'name' => 'Name',
            'remark' => 'Remark',
            'adder_id' => 'Adder ID',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
