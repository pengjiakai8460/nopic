<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "{{%xm_b_section_material}}".
 *
 * @property int $id 课程小节和素材关系ID
 * @property int $section_id 课程小节ID
 * @property int $material_id 素材ID
 * @property int $sort 排序
 */
class XmBSectionMaterial extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%xm_b_section_material}}';
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
            [['section_id', 'material_id'], 'required'],
            [['section_id', 'material_id', 'sort'], 'integer'],
            [['section_id', 'material_id'], 'unique', 'targetAttribute' => ['section_id', 'material_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section_id' => 'Section ID',
            'material_id' => 'Material ID',
            'sort' => 'Sort',
        ];
    }
}
