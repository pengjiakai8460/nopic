<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_material}}".
 *
 * @property int $id 素材ID
 * @property string $title 素材名字
 * @property int $type 素材类型1:image 2:text 3:vedio 4:sb2
 * @property string $value 素材值
 * @property string $attr 素材attr
 * @property int $status 素材状态：0禁用 1：启用
 * @property int $add_time 创建时间
 * @property int $update_time 修改时间
 */
class XmBMaterial extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_material}}';
    }

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
    public function rules()
    {
        return [
            [['title', 'value', 'add_time'], 'required'],
            [['type', 'status', 'add_time', 'update_time'], 'integer'],
            [['value'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['attr'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'type' => 'Type',
            'value' => 'Value',
            'attr' => 'Attr',
            'status' => 'Status',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
