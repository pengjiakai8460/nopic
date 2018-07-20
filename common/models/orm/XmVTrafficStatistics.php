<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_traffic_statistics".
 *
 * @property int $id
 * @property string $url 统计的链接
 * @property int $year 统计的年份
 * @property int $month 月份
 * @property int $day 日
 * @property string $chn 渠道编号
 * @property int $count 访问总数
 */
class XmVTrafficStatistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_traffic_statistics';
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
            [['year', 'month', 'day', 'count'], 'integer'],
            [['url'], 'string', 'max' => 255],
            [['chn'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'chn' => 'Chn',
            'count' => 'Count',
        ];
    }
}
