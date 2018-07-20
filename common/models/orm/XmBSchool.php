<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_school}}".
 *
 * @property int $id 学校ID
 * @property string $name 学校名称
 * @property int $province 省份
 * @property int $city 城市
 * @property int $area 地区
 * @property string $creator 创建者
 * @property int $system 学制
 * @property int $nature 性质
 * @property int $group_id 群组
 * @property string $logo 学校logo
 * @property int $status 状态
 * @property int $add_time 创建日期
 * @property int $update_time 修改日期
 */
class XmBSchool extends \yii\db\ActiveRecord
{
    
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db6');
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_school}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'creator', 'add_time'], 'required'],
            [['province', 'city', 'area', 'system', 'nature', 'group_id', 'status', 'add_time', 'update_time'], 'integer'],
            [['name', 'creator'], 'string', 'max' => 45],
            [['logo'], 'string', 'max' => 255],
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
            'province' => 'Province',
            'city' => 'City',
            'area' => 'Area',
            'creator' => 'Creator',
            'system' => 'System',
            'nature' => 'Nature',
            'group_id' => 'Group ID',
            'logo' => 'Logo',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
