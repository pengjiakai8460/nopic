<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_c_qusetion_tags".
 *
 * @property int $id 主键ID
 * @property int $q_id 问题ID
 * @property int $tag_id 标签ID
 * @property int $adder_id 作者ID
 * @property int $status 1:正常  0:删除
 * @property int $add_time 创建时间
 * @property int $update_time 最近一次更新时间
 */
class XmCQuestionTags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_c_question_tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q_id', 'tag_id', 'adder_id'], 'required'],
            [['q_id', 'tag_id', 'adder_id', 'status', 'add_time', 'update_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'q_id' => 'Q ID',
            'tag_id' => 'Tag ID',
            'adder_id' => 'Adder ID',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
