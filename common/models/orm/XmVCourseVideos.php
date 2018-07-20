<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_v_course_videos".
 *
 * @property int $id
 * @property string $title 视频标题
 * @property int $chapterId 章节ID
 * @property string $videoId 阿里视频存储地址
 * @property string $images 视频封面
 * @property int $createdTime 创建时间
 * @property int $creator 创建者ID
 */
class XmVCourseVideos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_v_course_videos';
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
            //[['summray'],'default','value' => 0],
            //[['title', 'src'], 'string'],
            [['videoId','chapterId'], 'required'],
            [['createdTime','videoId','chapterId'], 'integer'],
            [['videoId'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
   /* public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'summray' => 'Summray',
            'videoId' => 'Video ID',
            'src' => 'src',
            'createdTime' => 'Created Time',
            'creator' => 'Creator',
            'updatedTime' => 'updatedTime'
        ];
    }*/
}
