<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_testlets_question".
 *
 * @property int $id
 * @property int $t_id 题组ID
 * @property int $q_id 问题ID
 * @property int $sort 排序
 * @property int $adder_id 作者ID
 * @property int $status 1:正常  0:删除
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次修改时间
 */
class XmCTestletsQuestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_testlets_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['t_id', 'q_id'], 'required'],
            [['t_id', 'q_id', 'sort', 'adder_id', 'status', 'add_time', 'update_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            't_id' => 'T ID',
            'q_id' => 'Q ID',
            'sort' => 'Sort',
            'adder_id' => 'Adder ID',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
