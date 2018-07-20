<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_role".
 *
 * @property int $id
 * @property string $name
 * @property int $pid
 * @property int $status
 * @property string $remark
 * @property string $ename
 * @property int $create_time
 * @property int $update_time
 */
class XmRole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_role';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'create_time', 'update_time'], 'required'],
            [['pid', 'create_time', 'update_time'], 'integer'],
            [['name'], 'string', 'max' => 30],
            [['status'], 'string', 'max' => 1],
            [['remark'], 'string', 'max' => 255],
            [['ename'], 'string', 'max' => 5],
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
            'pid' => 'Pid',
            'status' => 'Status',
            'remark' => 'Remark',
            'ename' => 'Ename',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
