<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_user_rate".
 *
 * @property int $id
 * @property int $uid 用户UID
 * @property int $type1_right 单项选择题正确数目
 * @property int $type1_all 单项选择题总数目
 * @property int $type2_right 问题求解正确数目
 * @property int $type2_all 问题求解总数目
 * @property int $type3_right 阅读程序正确数目
 * @property int $type3_all 阅读程序总数目
 * @property int $type4_right 完善程序正确数目
 * @property int $type4_all 完善程序总数目
 * @property int $status 1:正常  0:已删除
 * @property int $add_time 添加时间
 * @property int $update_time 最近一次修改时间
 */
class XmUserRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_user_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'type1_right', 'type1_all', 'type2_right', 'type2_all', 'type3_right', 'type3_all', 'type4_right', 'type4_all', 'status', 'add_time', 'update_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'type1_right' => 'Type1 Right',
            'type1_all' => 'Type1 All',
            'type2_right' => 'Type2 Right',
            'type2_all' => 'Type2 All',
            'type3_right' => 'Type3 Right',
            'type3_all' => 'Type3 All',
            'type4_right' => 'Type4 Right',
            'type4_all' => 'Type4 All',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
