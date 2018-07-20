<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_videos".
 *
 * @property int $id
 * @property string $summray 简介
 * @property string $title 视频标题
 * @property string $src 图片保存地址
 * @property string $videoId 阿里视频ID
 * @property int $total_time 视频总时长
 * @property int $creator 创建者
 * @property int $createdTime
 * @property int $updatedTime
 * @property string $qiniuUrl 七牛云视频播放地址
 */
class XmVVideos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_videos';
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
            [['summray'], 'string'],
            //[['title', 'videoId', 'total_time'], 'required'],
            [['title', 'total_time'], 'required'],
            //[['total_time', 'creator', 'createdTime', 'updatedTime'], 'integer'],
            [['creator', 'createdTime', 'updatedTime'], 'integer'],
            [['title', 'qiniuUrl'], 'string', 'max' => 255],
            [['src'], 'string', 'max' => 150],
            //[['videoId'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'summray' => 'Summray',
            'title' => 'Title',
            'src' => 'Src',
            'videoId' => 'Video ID',
            //'total_time' => 'Total Time',
            'creator' => 'Creator',
            'createdTime' => 'Created Time',
            'updatedTime' => 'Updated Time',
            'qiniuUrl' => 'Qiniu Url',
        ];
    }
}
