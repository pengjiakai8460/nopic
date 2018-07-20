<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_exam_testlets".
 *
 * @property int $id
 * @property int $e_id 试卷ID
 * @property int $t_id 题组ID
 * @property int $sort 排序
 * @property int $adder_id 作者ID
 * @property int $status 1:正常  0:删除
 * @property int $add_time 创建时间
 * @property int $update_time
 */
class XmCExamTestlets extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_exam_testlets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['e_id', 't_id'], 'required'],
            [['e_id', 't_id', 'sort', 'adder_id', 'status', 'add_time', 'update_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'e_id' => 'E ID',
            't_id' => 'T ID',
            'sort' => 'Sort',
            'adder_id' => 'Adder ID',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
