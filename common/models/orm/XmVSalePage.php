<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_sale_page".
 *
 * @property int $id
 * @property int $courseId 课程id
 * @property string $title 标题
 * @property string $summary 摘要
 * @property string $headMap 头图
 * @property string $bottomMap 底图
 * @property string $coverMap 封面图
 * @property string $detailMap 详情图（多张）
 * @property int $status 状态: 1,正常; 0,隐藏; -1,删除
 * @property int $createTime 创建时间
 * @property int $updateTime 更新时间
 */
class XmVSalePage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_sale_page';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db4');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['courseId', 'createTime', 'updateTime'], 'required'],
            [['courseId', 'status', 'createTime', 'updateTime'], 'integer'],
            [['summary', 'headMap', 'bottomMap', 'coverMap', 'detailMap'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'courseId' => 'Course ID',
            'title' => 'Title',
            'summary' => 'Summary',
            'headMap' => 'Head Map',
            'bottomMap' => 'Bottom Map',
            'coverMap' => 'Cover Map',
            'detailMap' => 'Detail Map',
            'status' => 'Status',
            'createTime' => 'Create Time',
            'updateTime' => 'Update Time',
        ];
    }
}
