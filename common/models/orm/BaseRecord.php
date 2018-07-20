<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/16
 * Time: 12:29
 */

namespace common\models\orm;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class BaseRecord extends ActiveRecord
{
    const SOFT_DELETE_KEY = 'deleted_at';
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public static function deleteAll($condition = null, $params = [])
    {
        return static::updateAll([self::SOFT_DELETE_KEY => time()], $condition, $params);
    }
}